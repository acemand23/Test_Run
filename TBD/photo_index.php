  <!DOCTYPE html>
<html>
<head>
  <title></title>
<link rel="stylesheet" type="text/css" href="css/styles.css">
<link rel="stylesheet" type="text/css" href="css/menu-styles.css">
<link rel="stylesheet" type="text/css" href="css/ph_styles.css">

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
        </table>
<table style="width: 100%; border=1">
    <tr>
        <td style="text-align: middle">
            
            <style>
                .thumbnail {
                    width: 100px;
                    height: 100px;
                    margin: 10px;
                }
            </style>

                        <script>
                var thumbnails = document.getElementsByClassName("thumbnail");
                for (var i = 0; i < thumbnails.length; i++) {
                    thumbnails[i].addEventListener("click", showFullImage);
                }

                function showFullImage(event) {
                    event.preventDefault();
                    var imageSrc = this.parentNode.href;
                    var fullImage = document.createElement("img");
                    fullImage.src = imageSrc;

                    var overlay = document.createElement("div");
                    overlay.style.position = "fixed";
                    overlay.style.top = "0";
                    overlay.style.left = "0";
                    overlay.style.width = "100%";
                    overlay.style.height = "100%";
                    overlay.style.backgroundColor = "rgba(0, 0, 0, 0.7)";
                    overlay.style.display = "flex";
                    overlay.style.alignItems = "center";
                    overlay.style.justifyContent = "center";

                    overlay.appendChild(fullImage);
                    document.body.appendChild(overlay);

                    overlay.addEventListener("click", function() {
                        document.body.removeChild(overlay);
                    });
                }
            </script>
            
        </td>
    </tr>
</table>
</body>
</html>


