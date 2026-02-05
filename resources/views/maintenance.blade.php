<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f172a;
            --accent: #3b82f6;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 2rem;
        }

        .container {
            max-width: 600px;
            background: white;
            padding: 3rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .icon-box {
            width: 80px;
            height: 80px;
            background: #eff6ff;
            color: var(--accent);
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }

        h1 {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            color: var(--primary);
            letter-spacing: -0.025em;
        }

        p {
            font-size: 1.125rem;
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
        }

        .logo {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
            display: block;
        }

        .footer {
            margin-top: 1rem;
            padding-top: 2rem;
            border-top: 1px solid #f1f5f9;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            opacity: 0.9;
        }

        .countdown-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2.5rem;
        }

        .countdown-item {
            background: #f1f5f9;
            padding: 1rem;
            border-radius: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .countdown-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .countdown-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.05em;
        }

        @media (max-width: 480px) {
            .countdown-container {
                gap: 0.5rem;
            }
            .countdown-item {
                padding: 0.75rem 0.5rem;
            }
            .countdown-value {
                font-size: 1.25rem;
            }
            .countdown-label {
                font-size: 0.625rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <span class="logo">{{ config('app.name') }}</span>
        <div class="icon-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
        </div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>

        @if($countdownEnabled && $endTime)
            <div id="countdown" class="countdown-container">
                <div class="countdown-item">
                    <span class="countdown-value" id="days">00</span>
                    <span class="countdown-label">Days</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-value" id="hours">00</span>
                    <span class="countdown-label">Hours</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-value" id="minutes">00</span>
                    <span class="countdown-label">Minutes</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-value" id="seconds">00</span>
                    <span class="countdown-label">Seconds</span>
                </div>
            </div>

            <script>
                const endTime = new Date("{{ $endTime }}").getTime();
                
                function updateCountdown() {
                    const now = new Date().getTime();
                    const distance = endTime - now;
                    
                    if (distance < 0) {
                        clearInterval(x);
                        window.location.reload();
                        return;
                    }
                    
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    document.getElementById("days").innerText = String(days).padStart(2, '0');
                    document.getElementById("hours").innerText = String(hours).padStart(2, '0');
                    document.getElementById("minutes").innerText = String(minutes).padStart(2, '0');
                    document.getElementById("seconds").innerText = String(seconds).padStart(2, '0');
                }
                
                const x = setInterval(updateCountdown, 1000);
                updateCountdown();
            </script>
        @endif
        
        <div class="footer">
            <a href="/login" class="btn">Admin Login</a>
        </div>
    </div>
</body>
</html>
