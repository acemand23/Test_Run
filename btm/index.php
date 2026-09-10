<?php
// --- Boomtown Classic registration doorway ----------------------------------
// Registration opens Sep 10, 2026 at 6:00pm Central (America/Chicago resolves
// to CDT automatically). The live countdown now lives on the registration page
// itself; this page is an evergreen signpost that sends visitors there. We
// still format the open day/time from the DateTime so the displayed weekday and
// time can never drift out of sync with the actual open date.
$boomtown_open    = new DateTime('2026-09-10 18:00:00', new DateTimeZone('America/Chicago'));
$boomtown_reg_url = 'https://boomtown.vballmanager.com/org/event.php?slug=boomtown&event_id=128&instance_id=131';
$boomtown_dayline = $boomtown_open->format('l, F j');   // e.g. Thursday, September 10
$boomtown_time    = $boomtown_open->format('g:i A T');  // e.g. 6:00 PM CDT
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

        /* 10th-anniversary t-shirt perk, tucked under the Cost line */
        .cost-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 10px;
            padding: 12px 16px;
            background: #FFF3E7;
            border-left: 4px solid #FB7A3C;
            border-radius: 10px;
            font-size: 0.98em;
            line-height: 1.5;
            color: #7a4a24;
        }

        .cost-note .cost-note-icon {
            flex: 0 0 auto;
            font-size: 1.3em;
            line-height: 1.3;
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

        /* Doorway sub-line under the "Registration opens…" heading */
        .doorway-sub {
            max-width: 640px;
            margin: 0 auto 20px;
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

        /* Pre-register login CTA — warm sunset accent to stand apart from the
           page's cool indigo. Golden-hour sand for a beach tournament. */
        .prereg {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 20px;
            text-align: left;
            max-width: 720px;
            margin: 28px auto 4px;
            padding: 20px 26px;
            border-radius: 18px;
            color: #fff;
            background: linear-gradient(135deg, #FBA015 0%, #FB5A34 100%);
            box-shadow: 0 12px 30px rgba(251, 90, 52, 0.38);
        }

        .prereg > * {
            position: relative;
            z-index: 1;
        }

        /* Single, slow light sweep to draw the eye (disabled if reduced motion). */
        .prereg::after {
            content: "";
            position: absolute;
            top: 0;
            left: -60%;
            width: 45%;
            height: 100%;
            background: linear-gradient(100deg, transparent, rgba(255, 255, 255, 0.45), transparent);
            transform: skewX(-20deg);
            animation: prereg-sheen 4.2s ease-in-out infinite;
            z-index: 0;
        }

        @keyframes prereg-sheen {
            0%        { left: -60%; }
            55%, 100% { left: 135%; }
        }

        .prereg-icon {
            flex: 0 0 auto;
            width: 56px;
            height: 56px;
            display: grid;
            place-items: center;
            font-size: 1.9em;
            line-height: 1;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.22);
            box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.4);
        }

        .prereg-text {
            flex: 1 1 auto;
        }

        .prereg-title {
            font-size: 1.2em;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: 0.2px;
        }

        .prereg-sub {
            font-size: 0.98em;
            line-height: 1.45;
            margin-top: 5px;
            opacity: 0.95;
        }

        .prereg-btn {
            flex: 0 0 auto;
            display: inline-block;
            white-space: nowrap;
            background: #fff;
            color: #E8531F;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 50px;
            font-size: 1.05em;
            font-weight: 800;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.18);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .prereg-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.28);
        }

        @media (prefers-reduced-motion: reduce) {
            .prereg::after { animation: none; display: none; }
            .prereg-btn { transition: none; }
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

            .register-btn {
                padding: 14px 34px;
                font-size: 1.15em;
            }

            .prereg {
                flex-direction: column;
                text-align: center;
                gap: 14px;
                padding: 24px 20px;
            }

            .prereg-btn {
                width: 100%;
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
        <div class="countdown-card">
            <div class="countdown-heading">Registration opens <?php echo htmlspecialchars($boomtown_dayline, ENT_QUOTES); ?></div>
            <p class="doorway-sub">Head over to the registration page to watch the live countdown &mdash; and be ready to grab your spot the moment sign-ups open at <?php echo htmlspecialchars($boomtown_time, ENT_QUOTES); ?>.</p>
            <a class="register-btn" href="<?php echo htmlspecialchars($boomtown_reg_url, ENT_QUOTES); ?>">Head to the Registration Page &rarr;</a>
        </div>
        <div class="prereg">
            <div class="prereg-icon" aria-hidden="true">&#9889;</div>
            <div class="prereg-text">
                <div class="prereg-title">Pre-register your Boomtown login</div>
                <div class="prereg-sub">Don&rsquo;t forget to set up your Boomtown account ahead of time &mdash; it saves you time when sign-ups open.</div>
            </div>
            <a class="prereg-btn" href="https://boomtown.vballmanager.com/members/join.php">Pre-Register Now</a>
        </div>
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
            <li><strong>Cost:</strong> $44 per player (Matt and Sunday were both 34 years old, and this is the 10th Anniversary of the Event)
                <div class="cost-note">
                    <span class="cost-note-icon" aria-hidden="true">&#128085;</span>
                    <span>For our 10th Anniversary, every entry comes with a commemorative t-shirt (will be based on your profile size). Additional shirts can be ordered during the registration process.</span>
                </div>
            </li>
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
</script>

</body>
</html>
