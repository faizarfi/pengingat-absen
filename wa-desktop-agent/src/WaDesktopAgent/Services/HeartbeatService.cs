using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;

namespace WaDesktopAgent.Services;

public class HeartbeatService
{
    private readonly DatabaseService _databaseService;
    private readonly LaravelApiService _apiService;
    private readonly WhatsAppAutomationService _waService;
    private readonly ILogger<HeartbeatService> _logger;
    private readonly string _mode;
    private readonly string _agentName;

    public HeartbeatService(
        DatabaseService databaseService,
        LaravelApiService apiService,
        WhatsAppAutomationService waService,
        IConfiguration configuration,
        ILogger<HeartbeatService> logger)
    {
        _databaseService = databaseService;
        _apiService = apiService;
        _waService = waService;
        _logger = logger;
        _mode = configuration["Agent:Mode"] ?? "Database";
        _agentName = configuration["Agent:Name"] ?? "default";
    }

    public async Task SendHeartbeatAsync()
    {
        try
        {
            var isWaReady = _waService.IsWhatsAppRunning();

            if (_mode.Equals("Api", StringComparison.OrdinalIgnoreCase))
            {
                await _apiService.SendHeartbeatAsync(_agentName, isWaReady);
            }
            else
            {
                await _databaseService.UpdateHeartbeatAsync(_agentName, isWaReady);
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Heartbeat failed.");
        }
    }
}
