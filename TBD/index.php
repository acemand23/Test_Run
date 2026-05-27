  <!DOCTYPE html>
<html>
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="pragma" content="no-cache" />
<head>
  <title></title>
<link rel="stylesheet" type="text/css" href="css/styles.css">
<link rel="stylesheet" type="text/css" href="css/menu-styles.css">

</head>
<body>
     <style>
.countdown {
  font-weight: bold;
  font-size: 1.2em;
  color: #ff4444;
  animation: pulse 1s infinite alternate;
}

@keyframes pulse {
  from { color: #ff4444; transform: scale(1); }
  to   { color: #ffaa00; transform: scale(1.1); }
}

.countdown-done {
  font-weight: bold;
  font-size: 1.4em;
  color: #48BB8C;
  animation: glow 1s infinite alternate;
}

@keyframes glow {
  from { text-shadow: 0 0 5px #48BB8C; }
  to   { text-shadow: 0 0 20px #00ffcc; }
}
</style>
<body>
    
    <table id="myTable" style="height: 150px; ">
          <tr style="padding-bottom: 50px;">
            <td style="text-align: left; padding-right: 100px; padding-left: 50px; vertical-align: top;">
              <img class="resize-img" src="images/t1.png" alt="Logo">
            </td>
            <td style="text-align: right; vertical-align: top;">
            <!--    <a href="reg_index.php" ><img class="resize-img" src="images/t2.png" alt="Register Now"></a>-->
            </td>
            <td style="text-align: right; vertical-align: top;">
             <a href="spon_index.php" ><img class="resize-img" src="images/t3.png" alt="Sponsor Now"></a>
            </td>
            <td style="text-align: right; vertical-align: top;">
             <!--<a href="don_index.php" ><img class="resize-img" src="images/t6.png" alt="Donate Now"></a> -->
            </td>
            <td style="text-align: right; vertical-align: middle; padding-right: 50px">
                <div class="menu-overlay">
                <label for="menu-toggle" class="menu-icon">
                    <img src="images/t4.png" alt="Menu">
                </label>
                <nav class="menu">
                <!-- Menu items -->
                <ul>
                    <li style="style18g"><a href="index.php" style="style18g">HOME</a></li>
                   <!--  <li style="style18g"><a href="reg_index.php">REGISTER</a></li>-->
                  <li style="style18g"><a href="spon_index.php">SPONSOR</a></li>
                   <!--  <li style="style18g"><a href="don_index.php">DONATE</a></li> -->
                    <li style="style18g"><a href="tour_index.php">TOURNAMENT INFO</a></li>
                    <li style="style18g"><a href="#contact">QUESTIONS?</a></li>
                </ul>
                </nav>
                </div>
                <script src="script.js"></script>
            </td>
          </tr>
          <tr style="padding-bottom: 50px;">
            <td></td>
          </tr>
        </table><table id="myTable">
  <tr>
    <td style="background-image: url('images/sand.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
      <table>
        <tr>
          <td style="padding-left: 50px;">
            <p class="style30" id="top">Blind Draw 6 VS 6 Beach Volleyball Tournament</p>
          </td>
        </tr>
        <tr>
          <td style="padding-left: 25px;">
            <img class="resize-img" src="images/VG1.png" alt="Volleyball for Good">
          </td>
        </tr>
        <tr>
          <td style="padding-left: 50px;">
            <p class="style30">
              November 1st, 2025 / Aussies Grill & Beach Bar / 9am - 6pm -
              <span id="reg-text" class="countdown">Registration Has Closed</span>
            </p>
            <script>
              (function () {
                const openAt = new Date("2025-09-18T17:00:00-05:00");
                const el = document.getElementById("reg-text");

                function updateCountdown() {
                  const now = new Date();
                  const diffMs = openAt - now;

                  if (diffMs <= 0) {
                    el.innerHTML = '<a href="https://tbdvolleyball.com/register.php">CLICK HERE - REGISTER NOW</a>';
                    el.classList.remove("countdown");
                    el.classList.add("countdown-done");
                    clearInterval(timer);
                    return;
                  }

                  const totalSeconds = Math.floor(diffMs / 1000);
                  const days = Math.floor(totalSeconds / (3600 * 24));
                  const hours = Math.floor((totalSeconds % (3600 * 24)) / 3600);
                  const mins = Math.floor((totalSeconds % 3600) / 60);
                  const secs = totalSeconds % 60;

                  el.textContent = `Registration opens in ${days}d ${hours}h ${mins}m ${secs}s`;
                }

                updateCountdown();
                const timer = setInterval(updateCountdown, 1000);
              })();
            </script>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table>
        <tr>
          <td>
            <table>
              <tr>
                <td>
                  <p class="style30">Our Mission</p>
                </td>
              </tr>
              <tr>
                <td>
                  <p class="style18">Our mission is to connect the world of Beach Volleyball with organizations that positively impact our community, similar to the way volleyball has impacted our lives. The format intentionally fosters community by randomly pairing 6 players of all skill levels together. Volleyball exemplifies teamwork, as the most skilled player on the court can only succeed as much as they can support their least experienced teammate. If we approach our communities with this mindset, we can create a brighter future for all. Our goal is to establish lasting connections and inspire engagement and awareness.</p>
                </td>
              </tr>
              <tr>
                <td>
                  <br>
                  <p class="style18">You can learn more here:</p>
                  <font class="style18g"><a href="https://www.bigmentoring.org/">BIG BROTHER BIG SISTER OF CENTRAL TEXAS</a></font>
                </td>
              </tr>
            </table>
          </td>
          <td>
            <img class="resize-img" src="images/m1.png" alt="Mission">
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <p class="style80">ULTIMATELY, WE STRIVE FOR EVERYONE TO HAVE FUN, BUILD COMMUNITY, & CONTRIBUTE TO <font class="style80g">THE GREATER GOOD.</font></p>
    </td>
  </tr>
  <tr>
    <td style="text-align: center;">
      <img class="resize-img" src="images/sp1.png" alt="Sponsor Packages" id="sponsor">
    </td>
  </tr>
  <tr>
    <td style="background-image: url('images/sand.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
      <table>
        <tr>
          <td style="text-align: center;">
            <p class="style50"><font class="style50g"><a href="spon_index.php">CLICK HERE</a></font> TO START YOUR SPONSORSHIP</p>
          </td>
        </tr>
        <tr>
          <td style="text-align: center;">
            <img class="resize-img" src="images/ch1.png" alt=1779898716>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="text-align: center;">
      <img class="resize-img" src="images/py1.png" alt="Past Years">
    </td>
  </tr>
  <tr>
    <td style="text-align: center;">
      <a href="photo_index.php?year=2022"><img class="resize-img" src="images/py2a.png" alt="Button 1"></a>
      <a href="photo_index.php?year=2023"><img class="resize-img" src="images/py2b.png" alt="Button 2"></a>
    </td>
  </tr>
  <tr>
    <td style="background-color: #48BB8C; text-align: center; padding: 40px 0;">
      <p class="style50w" id="contact">CONTACT US</p>
      <p class="style30w">questions@tbdvolleyball.com</p>
      <img class="resize-img" width="50%" src="images/map.PNG" alt="map">
    </td>
  </tr>
</table>
</body>
</html>


1779898716