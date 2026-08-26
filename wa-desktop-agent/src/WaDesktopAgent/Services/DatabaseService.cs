using System.Data;
using Dapper;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;
using MySqlConnector;
using WaDesktopAgent.Models;

namespace WaDesktopAgent.Services;

public class DatabaseService
{
    private readonly string _connectionString;
    private readonly ILogger<DatabaseService> _logger;

    public DatabaseService(IConfiguration configuration, ILogger<DatabaseService> logger)
    {
        _connectionString = configuration.GetConnectionString("DefaultConnection") 
            ?? "Server=127.0.0.1;Port=3306;Database=pengingat_absen;User=root;Password=;";
        _logger = logger;
    }

    private IDbConnection CreateConnection() => new MySqlConnection(_connectionString);

    public async Task<IEnumerable<WaMessage>> GetPendingMessagesAsync(int limit = 10)
    {
        try
        {
            using var db = CreateConnection();
            const string sql = @"
                SELECT id, employee_id AS EmployeeId, phone_number AS PhoneNumber, 
                       message, type, status, attempts, scheduled_at AS ScheduledAt
                FROM wa_outbox
                WHERE status IN ('pending', 'retry')
                  AND (scheduled_at IS NULL OR scheduled_at <= NOW())
                ORDER BY scheduled_at ASC, id ASC
                LIMIT @Limit";

            return await db.QueryAsync<WaMessage>(sql, new { Limit = limit });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error querying pending messages from MySQL.");
            return Enumerable.Empty<WaMessage>();
        }
    }

    public async Task MarkProcessingAsync(long id)
    {
        try
        {
            using var db = CreateConnection();
            const string sql = @"
                UPDATE wa_outbox 
                SET status = 'processing', processing_at = NOW(), updated_at = NOW() 
                WHERE id = @Id";
            await db.ExecuteAsync(sql, new { Id = id });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error marking message {Id} as processing.", id);
        }
    }

    public async Task MarkSentAsync(long id)
    {
        try
        {
            using var db = CreateConnection();
            const string sql = @"
                UPDATE wa_outbox 
                SET status = 'sent', sent_at = NOW(), updated_at = NOW() 
                WHERE id = @Id";
            await db.ExecuteAsync(sql, new { Id = id });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error marking message {Id} as sent.", id);
        }
    }

    public async Task MarkFailedAsync(long id, string error, int maxRetry = 3)
    {
        try
        {
            using var db = CreateConnection();
            const string sql = @"
                UPDATE wa_outbox 
                SET attempts = attempts + 1,
                    status = IF(attempts + 1 >= @MaxRetry, 'failed', 'retry'),
                    last_error = @Error,
                    updated_at = NOW()
                WHERE id = @Id";
            await db.ExecuteAsync(sql, new { Id = id, Error = error, MaxRetry = maxRetry });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error marking message {Id} as failed.", id);
        }
    }

    public async Task UpdateHeartbeatAsync(string agentName, bool whatsappReady)
    {
        try
        {
            using var db = CreateConnection();
            const string sql = @"
                INSERT INTO wa_agent_heartbeats (agent_name, status, whatsapp_ready, last_seen_at, created_at, updated_at)
                VALUES (@AgentName, 'online', @WhatsappReady, NOW(), NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                    status = 'online', 
                    whatsapp_ready = @WhatsappReady, 
                    last_seen_at = NOW(), 
                    updated_at = NOW()";

            await db.ExecuteAsync(sql, new { AgentName = agentName, WhatsappReady = whatsappReady });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error updating agent heartbeat in database.");
        }
    }
}
