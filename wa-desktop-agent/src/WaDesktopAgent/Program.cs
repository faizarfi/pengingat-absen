using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;
using Serilog;
using WaDesktopAgent.Services;

namespace WaDesktopAgent;

public class Program
{
    [STAThread]
    public static async Task Main(string[] args)
    {
        // Setup Serilog
        Log.Logger = new LoggerConfiguration()
            .MinimumLevel.Information()
            .WriteTo.Console(outputTemplate: "[{Timestamp:HH:mm:ss} {Level:u3}] {Message:lj}{NewLine}{Exception}")
            .WriteTo.File("Logs/agent-.log", rollingInterval: RollingInterval.Day)
            .CreateLogger();

        try
        {
            var host = Host.CreateDefaultBuilder(args)
                .UseSerilog()
                .UseWindowsService()
                .ConfigureServices((hostContext, services) =>
                {
                    services.AddSingleton<DatabaseService>();
                    services.AddHttpClient<LaravelApiService>();
                    services.AddSingleton<WhatsAppAutomationService>();
                    services.AddSingleton<QueueService>();
                    services.AddSingleton<HeartbeatService>();
                    services.AddHostedService<Worker>();
                })
                .Build();

            await host.RunAsync();
        }
        catch (Exception ex)
        {
            Log.Fatal(ex, "WA Desktop Agent terminated unexpectedly.");
        }
        finally
        {
            Log.CloseAndFlush();
        }
    }
}
