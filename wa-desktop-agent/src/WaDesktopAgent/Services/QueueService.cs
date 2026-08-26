using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;
using WaDesktopAgent.Models;

namespace WaDesktopAgent.Services;

public class QueueService
{
    private readonly DatabaseService _databaseService;
    private readonly LaravelApiService _apiService;
    private readonly WhatsAppAutomationService _waService;
    private readonly ILogger<QueueService> _logger;
    private readonly string _mode;
    private readonly int _delayMin;
    private readonly int _delayMax;
    private readonly int _batchSize;
    private readonly int _cooldownMin;
    private readonly int _cooldownMax;
    private readonly int _maxRetry;
    private int _consecutiveSentCount = 0;

    public QueueService(
        DatabaseService databaseService,
        LaravelApiService apiService,
        WhatsAppAutomationService waService,
        IConfiguration configuration,
        ILogger<QueueService> logger)
    {
        _databaseService = databaseService;
        _apiService = apiService;
        _waService = waService;
        _logger = logger;
        _mode = configuration["Agent:Mode"] ?? "Database";
        _delayMin = int.TryParse(configuration["Agent:DelayMinSeconds"], out var dmin) ? dmin : 6;
        _delayMax = int.TryParse(configuration["Agent:DelayMaxSeconds"], out var dmax) ? dmax : 15;
        _batchSize = int.TryParse(configuration["Agent:BatchSize"], out var bs) ? bs : 10;
        _cooldownMin = int.TryParse(configuration["Agent:CooldownMinSeconds"], out var cmin) ? cmin : 60;
        _cooldownMax = int.TryParse(configuration["Agent:CooldownMaxSeconds"], out var cmax) ? cmax : 120;
        _maxRetry = int.TryParse(configuration["Agent:MaxRetry"], out var r) ? r : 3;
    }

    public async Task ProcessPendingMessagesAsync(CancellationToken cancellationToken)
    {
        var messages = _mode.Equals("Api", StringComparison.OrdinalIgnoreCase)
            ? await _apiService.GetPendingMessagesAsync(10)
            : await _databaseService.GetPendingMessagesAsync(10);

        var list = messages.ToList();
        if (!list.Any())
        {
            return;
        }

        _logger.LogInformation("Found {Count} pending message(s) to process in {Mode} mode.", list.Count, _mode);

        var isReady = await _waService.EnsureWhatsAppRunningAsync();
        if (!isReady)
        {
            _logger.LogWarning("WhatsApp Desktop is not ready. Skipping message sending batch.");
            return;
        }

        var random = new Random();

        foreach (var msg in list)
        {
            if (cancellationToken.IsCancellationRequested) break;

            _logger.LogInformation("Processing Outbox #{Id} for {Phone} (Type: {Type})...", msg.Id, msg.PhoneNumber, msg.Type);

            if (_mode.Equals("Api", StringComparison.OrdinalIgnoreCase))
                await _apiService.MarkProcessingAsync(msg.Id);
            else
                await _databaseService.MarkProcessingAsync(msg.Id);

            var success = await _waService.SendMessageAsync(msg.PhoneNumber, msg.Message);

            if (success)
            {
                if (_mode.Equals("Api", StringComparison.OrdinalIgnoreCase))
                    await _apiService.MarkSentAsync(msg.Id);
                else
                    await _databaseService.MarkSentAsync(msg.Id);

                _consecutiveSentCount++;
                _logger.LogInformation("Successfully sent message #{Id} to {Phone}.", msg.Id, msg.PhoneNumber);
            }
            else
            {
                var err = "Failed to send via WhatsApp Desktop Automation.";
                if (_mode.Equals("Api", StringComparison.OrdinalIgnoreCase))
                    await _apiService.MarkFailedAsync(msg.Id, err);
                else
                    await _databaseService.MarkFailedAsync(msg.Id, err, _maxRetry);

                _logger.LogWarning("Failed to send message #{Id} to {Phone}.", msg.Id, msg.PhoneNumber);
            }

            // Anti-Ban Cooldown / Jitter Delay
            if (_consecutiveSentCount >= _batchSize)
            {
                var cooldown = random.Next(_cooldownMin, _cooldownMax + 1);
                _logger.LogInformation("Sent {Count} consecutive messages. Taking batch cooldown break of {Cooldown}s...", _consecutiveSentCount, cooldown);
                await Task.Delay(TimeSpan.FromSeconds(cooldown), cancellationToken);
                _consecutiveSentCount = 0;
            }
            else
            {
                var randomDelay = random.Next(_delayMin, _delayMax + 1);
                _logger.LogInformation("Random anti-ban delay of {Delay}s before next message...", randomDelay);
                await Task.Delay(TimeSpan.FromSeconds(randomDelay), cancellationToken);
            }
        }
    }
}
