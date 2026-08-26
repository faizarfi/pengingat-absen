namespace WaDesktopAgent.Models;

public class AgentStatus
{
    public string AgentName { get; set; } = "default";
    public string Status { get; set; } = "online";
    public bool WhatsappReady { get; set; }
    public DateTime LastSeenAt { get; set; } = DateTime.UtcNow;
    public Dictionary<string, object> Metadata { get; set; } = new();
}
