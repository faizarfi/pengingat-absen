using System.Net.Http.Headers;
using System.Net.Http.Json;
using System.Text.Json;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;
using WaDesktopAgent.Models;

namespace WaDesktopAgent.Services;

public class LaravelApiService
{
    private readonly HttpClient _httpClient;
    private readonly ILogger<LaravelApiService> _logger;
    private readonly string _baseUrl;
    private readonly string _token;

    public LaravelApiService(HttpClient httpClient, IConfiguration configuration, ILogger<LaravelApiService> logger)
    {
        _httpClient = httpClient;
        _logger = logger;
        _baseUrl = (configuration["LaravelApi:BaseUrl"] ?? "http://localhost:8000").TrimEnd('/');
        _token = configuration["LaravelApi:Token"] ?? "change-this-token-to-something-secure";

        _httpClient.BaseAddress = new Uri(_baseUrl);
        _httpClient.DefaultRequestHeaders.Authorization = new AuthenticationHeaderValue("Bearer", _token);
        _httpClient.DefaultRequestHeaders.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));
    }

    public async Task<IEnumerable<WaMessage>> GetPendingMessagesAsync(int limit = 10)
    {
        try
        {
            var response = await _httpClient.GetAsync($"/api/agent/messages?limit={limit}");
            if (!response.IsSuccessStatusCode)
            {
                _logger.LogWarning("API returned status code: {StatusCode}", response.StatusCode);
                return Enumerable.Empty<WaMessage>();
            }

            var result = await response.Content.ReadFromJsonAsync<JsonElement>();
            if (result.TryGetProperty("data", out var dataElement) && dataElement.ValueKind == JsonValueKind.Array)
            {
                var messages = new List<WaMessage>();
                foreach (var item in dataElement.EnumerateArray())
                {
                    messages.Add(new WaMessage
                    {
                        Id = item.GetProperty("id").GetInt64(),
                        EmployeeId = item.TryGetProperty("employee_id", out var empId) ? empId.GetInt64() : 0,
                        PhoneNumber = item.GetProperty("phone_number").GetString() ?? "",
                        Message = item.GetProperty("message").GetString() ?? "",
                        Type = item.TryGetProperty("type", out var type) ? (type.GetString() ?? "manual") : "manual",
                        Status = item.TryGetProperty("status", out var st) ? (st.GetString() ?? "pending") : "pending",
                        Attempts = item.TryGetProperty("attempts", out var att) ? att.GetInt32() : 0
                    });
                }
                return messages;
            }

            return Enumerable.Empty<WaMessage>();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to fetch pending messages via REST API.");
            return Enumerable.Empty<WaMessage>();
        }
    }

    public async Task MarkProcessingAsync(long id)
    {
        try
        {
            await _httpClient.PostAsync($"/api/agent/messages/{id}/processing", null);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to mark message {Id} processing via REST API.", id);
        }
    }

    public async Task MarkSentAsync(long id)
    {
        try
        {
            await _httpClient.PostAsync($"/api/agent/messages/{id}/sent", null);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to mark message {Id} sent via REST API.", id);
        }
    }

    public async Task MarkFailedAsync(long id, string error)
    {
        try
        {
            var content = JsonContent.Create(new { error });
            await _httpClient.PostAsync($"/api/agent/messages/{id}/failed", content);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to mark message {Id} failed via REST API.", id);
        }
    }

    public async Task SendHeartbeatAsync(string agentName, bool whatsappReady)
    {
        try
        {
            var content = JsonContent.Create(new
            {
                agent_name = agentName,
                whatsapp_ready = whatsappReady
            });
            await _httpClient.PostAsync("/api/agent/heartbeat", content);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to send heartbeat via REST API.");
        }
    }
}
