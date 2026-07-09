<?php
require_once __DIR__ . '/../db.php';
startSession();

// Determine the relative path prefix based on the current page
$isIndex = isset($CURRENT_PAGE) && $CURRENT_PAGE === 'index';
$pathPrefix = $isIndex ? '' : '../';

// Base URL for assets and links
$baseUrl = '/bundle/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <base href="<?php echo $baseUrl; ?>">
   <title>header</title>

   <!-- Font Awesome CSS -->
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">


        <!-- Stylesheets -->
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/plugin.min.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
    <link href="css/responsive.css" rel="stylesheet" />
    <link href="css/darkmode.css" rel="stylesheet" />

</head>

<body>
   <header id="mainHeader" class="header-pr nav-bg-b main-header navfix fixed-top menu-white">
      <div class="container-fluid m-pad">
         <div class="menu-header">
            <div class="dsk-logo">
               <a class="nav-brand" href="https://cultureofinternet.com/">
                  <img src="images/coi-ligth.png" alt="COI" class="mega-white-logo" />
                  <img src="images/coi-dark.png" alt="COI" class="mega-darks-logo" />
               </a>
            </div>
            <div class="custom-nav" role="navigation">
               <ul class="nav-list">
                  <li><a href="https://cultureofinternet.com/" class="menu-links nav-link">Home</a></li>
                  <li class="sbmenu rpdropdown">
                     <a href="about" class="menu-links nav-link">About</a>
                     <div class="nx-dropdown menu-dorpdown">
                        <div class="sub-menu-section">
                           <div class="sub-menu-center-block">
                              <div class="sub-menu-column smfull">
                                 <ul>
                                    <li><a href="about">About Us</a> </li>
                                    <li><a href="team">Meet the Team</a> </li>
                                 </ul>
                              </div>
                           </div>
                        </div>
                     </div>
                  </li>
                  <li class="sbmenu rpdropdown">
                     <a href="#" class="menu-links nav-link">Education</a>
                     <div class="nx-dropdown menu-dorpdown">
                        <div class="sub-menu-section">
                           <div class="sub-menu-center-block">
                              <div class="sub-menu-column smfull">
                                 <ul>
                                    <li><a href="digital-marketing-institute-delhi">Digital Marketing</a> </li>
                                    <li><a href="web-designing-institute-delhi">Web Designing</a> </li>
                                    <li><a href="full-stack-development-institute-delhi">Full Stack Development</a> </li>
                                    <li><a href="graphic-designing-institute-delhi">Graphic Designing</a> </li>
                                    <li><a href="data-analyst-institute-delhi">Data Analyst</a> </li>
                                    <li><a href="multimedia-institute-delhi">Multimedia</a></li>
                                 </ul>
                              </div>
                           </div>
                        </div>
                     </div>
                  </li>
                  <li><a href="pricings.php" class="menu-links nav-link">Pricings</a></li>
                  <li><a href="refer-and-earn" target="_blank" class="menu-links nav-link">Refer&Earn</a></li>
                  <li><a href="contact" class="menu-links nav-link">Contact Us</a></li>
               </ul>

               <ul class="nav-list right-end-btn">
                  <!-- Phone button replaced with Font Awesome -->
                   <li class="hidemobile">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo $baseUrl; ?>user-dashboard/dashboard.php" class="btn-br bg-btn3 btshad-b2 lnk" style="margin-right: 20px;">
                        Dashboard 
                     </a>
                     <?php else: ?>
                     <a href="<?php echo $baseUrl; ?>login-Sign-Up1.php" class="btn-br bg-btn3 btshad-b2 lnk" style="margin-right: 20px;">
                        Login/Signup 
                     </a>
                      <?php endif; ?>
                  </li>

                  <li class="hidemobile">
                     <a href="tel:8130840080" class="btn-round- btn-br bg-btn2">
                        <i class="fas fa-phone-alt"></i>
                     </a>
                  </li>
                  
                  <li class="hidedesktop">
                     <?php if (isLoggedIn()): ?>
                        <a href="<?php echo $baseUrl; ?>user-dashboard/dashboard.php" class="btn-br bg-btn3 btshad-b2 lnk">
                        Dashboard 
                     </a>
                     <?php else: ?>
                     <a href="<?php echo $baseUrl; ?>login-Sign-Up1.php" class="btn-br bg-btn3 btshad-b2 lnk">
                        Login/Signup 
                     </a>
                      <?php endif; ?>
                  </li>
                  <li class="navm- hidedesktop">
                     <a class="toggle" href="#"><span></span></a>
                  </li>
               </ul>
            </div>
         </div>

         <nav id="main-nav">
            <ul class="first-nav">
               <li><a href="https://cultureofinternet.com/">Home</a></li>
               <li>
                  <a href="#">About</a>
                  <ul>
                     <li><a href="about">About Us</a></li>
                     <li><a href="team">Meet the Team</a></li>
                  </ul>
               </li>
               <li>
                  <a href="">Education</a>
                  <ul>
                     <li><a href="digital-marketing-institute-delhi">Digital Marketing</a></li>
                     <li><a href="web-designing-institute-delhi">Web Designing</a></li>
                     <li><a href="full-stack-development-institute-delhi">Full Stack Development</a></li>
                     <li><a href="graphic-designing-institute-delhi">Graphic Designing</a></li>
                     <li><a href="data-analyst-institute-delhi">Data Analyst</a></li>
                     <li><a href="multimedia-institute-delhi">Multimedia</a></li>
                  </ul>
               </li>
               <li><a href="pricings.php" class="menu-links">Pricings</a></li>
               <li><a href="refer-and-earn.php" class="menu-links">Refer & Earn</a></li>
               <li><a href="contact" class="menu-links">Connect Us</a></li>
            </ul>

            <ul class="bottom-nav">
               <?php if (isLoggedIn()): ?>
                  <li class="prb">
                     <a href="<?php echo $baseUrl; ?>user-dashboard/dashboard.php" class="menu-links">
                        <i class="fas fa-user"></i> Dashboard
                     </a>
                  </li>
               <?php else: ?>
                  <li class="prb">
                     <a href="<?php echo $baseUrl; ?>login-Sign-Up1.php" class="menu-links">
                        <i class="fas fa-sign-in-alt"></i> Login/Signup
                     </a>
                  </li>
               <?php endif; ?>
               <li class="prb">
                  <a href="tel:8130840080">
                     <i class="fas fa-phone-alt"></i>
                  </a>
               </li>
               <li class="prb">
                  <a href="mailto:contactcultureofinternet@gmail.com">
                     <i class="fas fa-envelope"></i>
                  </a>
               </li>
            </ul>
         </nav>
      </div>
   </header>
</body>

    <script src="script.js"></script>

        <script src="js/jquery.min.js"></script>
        <script src="js/plugin.min.js"></script>
        <script src="js/main.js"></script>


</html>
