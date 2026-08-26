using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.Logging;
using WaDesktopAgent.Services;

namespace WaDesktopAgent;

public class Worker : BackgroundService
{
    private readonly ILogger<Worker> _logger;
    private readonly QueueService _queueService;
    private readonly HeartbeatService _heartbeatService;
    private readonly int _pollIntervalSeconds;
    private readonly int _heartbeatIntervalSeconds;

    public Worker(
        ILogger<Worker> logger,
        QueueService queueService,
        HeartbeatService heartbeatService,
        IConfiguration configuration)
    {
        _logger = logger;
        _queueService = queueService;
        _heartbeatService = heartbeatService;
        _pollIntervalSeconds = int.TryParse(configuration["Agent:PollIntervalSeconds"], out var p) ? p : 5;
        _heartbeatIntervalSeconds = int.TryParse(configuration["Agent:HeartbeatIntervalSeconds"], out var h) ? h : 15;
    }

    protected override async Task ExecuteAsync(CancellationToken stoppingToken)
    {
        _logger.LogInformation("==================================================");
        _logger.LogInformation("🚀 WA Desktop Agent started and running.");
        _logger.LogInformation("==================================================");

        var lastHeartbeat = DateTime.MinValue;

        while (!stoppingToken.IsCancellationRequested)
        {
            try
            {
                // 1. Send Heartbeat if due
                if ((DateTime.UtcNow - lastHeartbeat).TotalSeconds >= _heartbeatIntervalSeconds)
                {
                    await _heartbeatService.SendHeartbeatAsync();
                    lastHeartbeat = DateTime.UtcNow;
                }

                // 2. Process pending messages
                await _queueService.ProcessPendingMessagesAsync(stoppingToken);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Unexpected error in agent worker loop.");
            }

            await Task.Delay(TimeSpan.FromSeconds(_pollIntervalSeconds), stoppingToken);
        }

        _logger.LogInformation("WA Desktop Agent stopping.");
    }
}
