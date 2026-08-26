using System.Diagnostics;
using System.Runtime.InteropServices;
using System.Text;
using System.Web;
using System.Windows.Forms;
using Microsoft.Extensions.Logging;

namespace WaDesktopAgent.Services;

public class WhatsAppAutomationService
{
    private readonly ILogger<WhatsAppAutomationService> _logger;

    [DllImport("user32.dll")]
    private static extern bool SetForegroundWindow(IntPtr hWnd);

    [DllImport("user32.dll")]
    private static extern bool ShowWindow(IntPtr hWnd, int nCmdShow);

    [DllImport("user32.dll", SetLastError = true)]
    private static extern IntPtr FindWindow(string? lpClassName, string? lpWindowName);

    private const int SW_RESTORE = 9;
    private const int SW_SHOW = 5;

    public WhatsAppAutomationService(ILogger<WhatsAppAutomationService> logger)
    {
        _logger = logger;
    }

    public bool IsWhatsAppRunning()
    {
        var processes = Process.GetProcessesByName("WhatsApp");
        return processes.Length > 0;
    }

    public async Task<bool> EnsureWhatsAppRunningAsync()
    {
        if (IsWhatsAppRunning())
        {
            return true;
        }

        _logger.LogInformation("WhatsApp Desktop is not running. Attempting to start...");

        try
        {
            // Try starting via URI protocol
            var psi = new ProcessStartInfo
            {
                FileName = "whatsapp://",
                UseShellExecute = true
            };
            Process.Start(psi);

            // Wait for it to launch
            for (int i = 0; i < 15; i++)
            {
                await Task.Delay(1000);
                if (IsWhatsAppRunning())
                {
                    _logger.LogInformation("WhatsApp Desktop successfully started.");
                    await Task.Delay(3000); // Give it time to initialize UI
                    return true;
                }
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to start WhatsApp Desktop.");
        }

        return false;
    }

    public async Task<bool> SendMessageAsync(string phoneNumber, string message, int sendDelaySeconds = 5)
    {
        try
        {
            // Normalize phone number (ensure country code 62...)
            var cleanPhone = NormalizePhoneNumber(phoneNumber);

            _logger.LogInformation("Opening chat for {Phone}...", cleanPhone);

            // Open WhatsApp with direct chat URL protocol
            // Note: whatsapp://send?phone=...
            var uri = $"whatsapp://send?phone={cleanPhone}&text={HttpUtility.UrlEncode(message)}";
            
            var psi = new ProcessStartInfo
            {
                FileName = uri,
                UseShellExecute = true
            };
            Process.Start(psi);

            // Wait for WhatsApp window to focus and prefill the message
            await Task.Delay(3500);

            // Bring WhatsApp to foreground
            BringWhatsAppToFront();

            await Task.Delay(1000);

            // Simulate pressing ENTER to send the prefilled message
            SendKeys.SendWait("{ENTER}");

            _logger.LogInformation("Message sent to {Phone}.", cleanPhone);

            // Cool-down delay between messages
            await Task.Delay(TimeSpan.FromSeconds(Math.Max(1, sendDelaySeconds)));

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to automate WhatsApp send for {Phone}.", phoneNumber);
            return false;
        }
    }

    private void BringWhatsAppToFront()
    {
        var processes = Process.GetProcessesByName("WhatsApp");
        foreach (var process in processes)
        {
            if (process.MainWindowHandle != IntPtr.Zero)
            {
                ShowWindow(process.MainWindowHandle, SW_RESTORE);
                SetForegroundWindow(process.MainWindowHandle);
                return;
            }
        }
    }

    private static string NormalizePhoneNumber(string phone)
    {
        var digitsOnly = new string(phone.Where(char.IsDigit).ToArray());
        if (digitsOnly.StartsWith("0"))
        {
            return "62" + digitsOnly.Substring(1);
        }
        if (digitsOnly.StartsWith("8"))
        {
            return "62" + digitsOnly;
        }
        return digitsOnly;
    }
}
