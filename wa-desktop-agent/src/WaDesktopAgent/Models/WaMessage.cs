namespace WaDesktopAgent.Models;

public class WaMessage
{
    public long Id { get; set; }
    public long EmployeeId { get; set; }
    public string PhoneNumber { get; set; } = string.Empty;
    public string Message { get; set; } = string.Empty;
    public string Type { get; set; } = "manual";
    public string Status { get; set; } = "pending";
    public int Attempts { get; set; }
    public DateTime? ScheduledAt { get; set; }
    public DateTime? ProcessingAt { get; set; }
    public DateTime? SentAt { get; set; }
    public string? LastError { get; set; }
}
