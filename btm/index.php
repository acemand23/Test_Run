<?php
// --- Boomtown Classic registration countdown --------------------------------
// Registration opens Sep 10, 2026 at 6:00pm Central. Using the America/Chicago
// zone resolves that date to CDT (UTC-5) automatically. getTimestamp() and
// time() both return absolute UTC epoch seconds, so the comparison is correct
// regardless of the server's configured timezone.
$boomtown_open    = new DateTime('2026-09-10 18:00:00', new DateTimeZone('America/Chicago'));
$boomtown_target  = $boomtown_open->getTimestamp();
$boomtown_now     = time();
$boomtown_is_open = $boomtown_now >= $boomtown_target;
$boomtown_reg_url = 'https://boomtown.vballmanager.com/org/tournament.php?slug=boomtown&event_id=128';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boomtown Classic - 10th Anniversary</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .top-nav {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .top-nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
        }

        .top-nav a {
            text-decoration: none;
            color: #333;
            font-weight: 600;
            padding: 10px 15px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .top-nav a:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .header-image {
            text-align: center;
            margin-bottom: 40px;
        }

        .header-image img {
            max-width: 100%;
            height: auto;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .announcement-section {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .announcement-section h2 {
            font-size: 2em;
            color: #667eea;
            margin-bottom: 15px;
        }

        .announcement-section p {
            font-size: 1.2em;
            margin-bottom: 15px;
            color: #555;
            line-height: 1.6;
        }

        .anniversary-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 35px;
            border-radius: 50px;
            font-size: 1.4em;
            font-weight: bold;
            margin: 20px 0;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            letter-spacing: 1px;
        }

        .memorial-note {
            font-style: italic;
            color: #764ba2;
            font-size: 1.15em;
            margin-top: 10px;
        }

        .time-display {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .time-display p {
            font-size: 1.1em;
            color: #555;
        }

        #local-time {
            font-weight: bold;
            color: #667eea;
        }

        .event-info {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .event-info h2 {
            color: #667eea;
            margin-bottom: 25px;
            font-size: 2em;
            text-align: center;
        }

        .event-info ul {
            list-style: none;
            margin-bottom: 25px;
        }

        .event-info li {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            font-size: 1.1em;
        }

        .event-info li:last-child {
            border-bottom: none;
        }

        .event-info strong {
            color: #667eea;
            display: inline-block;
            width: 140px;
        }

        .event-info p {
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        /* Registration countdown */
        .countdown-card {
            margin: 28px 0 12px;
        }

        .countdown-heading {
            font-size: 1.05em;
            color: #764ba2;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 18px;
        }

        .countdown-timer {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .countdown-unit {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            padding: 18px 12px;
            min-width: 92px;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .countdown-number {
            display: block;
            font-size: 2.6em;
            font-weight: bold;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .countdown-label {
            display: block;
            font-size: 0.78em;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 8px;
            opacity: 0.9;
        }

        .register-btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            padding: 16px 45px;
            border-radius: 50px;
            font-size: 1.3em;
            font-weight: bold;
            letter-spacing: 1px;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }

        .register-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.5);
        }

        @media (max-width: 768px) {
            .top-nav ul {
                gap: 15px;
            }

            .event-info strong {
                width: auto;
                display: block;
                margin-bottom: 5px;
            }

            .announcement-section h2 {
                font-size: 1.5em;
            }

            .anniversary-badge {
                font-size: 1.1em;
                padding: 12px 25px;
            }

            .countdown-timer {
                gap: 10px;
            }

            .countdown-unit {
                min-width: 68px;
                padding: 14px 8px;
            }

            .countdown-number {
                font-size: 2em;
            }

            .register-btn {
                padding: 14px 34px;
                font-size: 1.15em;
            }
        }
    </style>
</head>
<body>

<!-- Navigation Menu -->
<nav class="top-nav">
   <ul>
        <li><a href="index.php">Main Page</a></li>
        <li><a href="about.php">About Matt & Sunday</a></li>
        <li><a href="fap.php">Financial Assistance Program</a></li>
        <li><a href="sponsors.php">Sponsors</a></li>
        <li><a href="contact.php">Contact Us</a></li>
        <li><a href="donate.php">Donations</a></li>
        <li><a href="swag.php">Boomtown Swag</a></li>
    </ul>
</nav>

<!-- Main Content -->
<div class="container">
    <div class="header-image">
        <img src="title.png" alt="Event Title Image"/>
    </div>

    <div class="announcement-section">
        <h2>Celebrating 10 Years of Honoring Matt & Sunday Rowan</h2>
        <div class="anniversary-badge">10th Anniversary Tournament</div>
        <p>We're excited to announce the 10th annual charity tournament in loving memory of our dear friends Matt and Sunday Rowan.</p>
        <p>Save the date: <strong>October 10th, 2026</strong></p>
        <?php if ($boomtown_is_open): ?>
            <div class="countdown-card">
                <div class="countdown-heading">Registration is open!</div>
                <a class="register-btn" href="<?php echo htmlspecialchars($boomtown_reg_url, ENT_QUOTES); ?>">Register Now</a>
            </div>
        <?php else: ?>
            <div class="countdown-card">
                <div class="countdown-heading">Registration opens in</div>
                <div id="boomtown-countdown" class="countdown-timer"
                     data-target="<?php echo $boomtown_target; ?>"
                     data-now="<?php echo $boomtown_now; ?>"
                     data-url="<?php echo htmlspecialchars($boomtown_reg_url, ENT_QUOTES); ?>">
                    <div class="countdown-unit"><span class="countdown-number" data-days>--</span><span class="countdown-label">Days</span></div>
                    <div class="countdown-unit"><span class="countdown-number" data-hours>--</span><span class="countdown-label">Hours</span></div>
                    <div class="countdown-unit"><span class="countdown-number" data-minutes>--</span><span class="countdown-label">Minutes</span></div>
                    <div class="countdown-unit"><span class="countdown-number" data-seconds>--</span><span class="countdown-label">Seconds</span></div>
                </div>
            </div>
        <?php endif; ?>
        <p class="memorial-note">All proceeds benefit the Dr. Matthew P. Rowan Memorial Foundation</p>
    </div>

    <!-- Time Display -->
    <div class="time-display">
        <p>Current Local Time: <span id="local-time">Loading...</span></p>
    </div>

    <div class="event-info">
        <h2>Event Information</h2>
        <ul>
            <li><strong>When:</strong> October 10th, 2026</li>
            <li><strong>Where:</strong> Sideliner's Grill, 15630 Henderson Pass, San Antonio, TX 78232</li>
            <li><strong>Check in:</strong> 7:30 - 8 a.m.</li>
            <li><strong>Play begins:</strong> 8:30 a.m. SHARP!</li>
            <li><strong>Format:</strong> Blind Draw 4's (sign up by yourself and you will be placed on a team)</li>
            <li><strong>Cost:</strong> $34 per player (Matt and Sunday were both 34 years old)</li>
        </ul>
        <p>Prizes for 1st and 2nd place teams (maybe more depending on prize donations).</p>
        <p>All proceeds go directly to the Dr. Matthew P. Rowan Memorial Foundation to foster and grow amateur beach volleyball communities across San Antonio and Austin.</p>
    </div>
</div>

<script>
(function() {
    const localTimeEl = document.getElementById('local-time');

    function updateLocalTime() {
        const now = new Date();
        const options = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        localTimeEl.textContent = now.toLocaleTimeString('en-US', options);
    }

    updateLocalTime();
    const timeInterval = setInterval(updateLocalTime, 1000);

    window.addEventListener('beforeunload', function() {
        clearInterval(timeInterval);
    });
})();

// Registration countdown — driven by the server clock, not the visitor's.
(function() {
    var wrap = document.getElementById('boomtown-countdown');
    if (!wrap) return;

    var targetMs    = parseInt(wrap.getAttribute('data-target'), 10) * 1000;
    var serverNowMs = parseInt(wrap.getAttribute('data-now'), 10) * 1000;
    var regUrl      = wrap.getAttribute('data-url');

    // Advance from the server's timestamp using elapsed monotonic time, so the
    // countdown ignores any skew in the visitor's device clock.
    var hasPerf   = !!(window.performance && performance.now);
    var loadedAt  = hasPerf ? performance.now() : Date.now();
    function nowMs() {
        return serverNowMs + ((hasPerf ? performance.now() : Date.now()) - loadedAt);
    }

    var daysEl = wrap.querySelector('[data-days]');
    var hrsEl  = wrap.querySelector('[data-hours]');
    var minsEl = wrap.querySelector('[data-minutes]');
    var secsEl = wrap.querySelector('[data-seconds]');

    function pad(n) { return (n < 10 ? '0' : '') + n; }

    var timer = null;

    function showRegister() {
        var card = wrap.parentNode;
        var heading = card.querySelector('.countdown-heading');
        if (heading) heading.textContent = 'Registration is open!';
        if (wrap.parentNode) wrap.parentNode.removeChild(wrap);
        var btn = document.createElement('a');
        btn.className = 'register-btn';
        btn.href = regUrl;
        btn.textContent = 'Register Now';
        card.appendChild(btn);
    }

    function tick() {
        var diff = targetMs - nowMs();
        if (diff <= 0) {
            if (timer) clearInterval(timer);
            showRegister();
            return;
        }
        var total = Math.floor(diff / 1000);
        daysEl.textContent = Math.floor(total / 86400);
        hrsEl.textContent  = pad(Math.floor((total % 86400) / 3600));
        minsEl.textContent = pad(Math.floor((total % 3600) / 60));
        secsEl.textContent = pad(total % 60);
    }

    tick();
    timer = setInterval(tick, 1000);

    window.addEventListener('beforeunload', function() {
        if (timer) clearInterval(timer);
    });
})();
</script>

</body>
</html>
