<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Unicare</title>
    <link rel="icon" href="../image/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="index.css" />
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header_left">
            <img src="image/logo.png" alt="">
            <h1>Unicare</h1>
        </div>
        <div class="spacer"></div>
        <div class="header_right">
            <div class="nav">
                <a href="#hero"><h1>HOME</h1></a>
                <a href="#about"><h1>ABOUT</h1></a>
                <a href="#features"><h1>FEATURES</h1></a>
                <a href="#contact"><h1>CONTACT</h1></a>
                <a href="Admin/index.php"><h1 style="background-color: #e8b45e; color: white; padding: 10px 20px; border-radius: 10px;">LOGIN</h1></a>
            </div>
            <div class="small_nav">
                <img src="image/icons/menu.png" alt="" onclick="toggleMenu()">
            </div>
        </div>
    </div>
    <div id="nav">
        <a href="#hero"><h1>HOME</h1></a>
        <a href="#about"><h1>ABOUT</h1></a>
        <a href="#features"><h1>FEATURES</h1></a>
        <a href="#contact"><h1>CONTACT</h1></a>
    </div>
    <div class="content">
        <div class="hero" id="hero">
            <img src="image/hero.png" alt="">
            <div class="hero_description">
                <div class="hero_text">
                    <h1>UNICARE</h1>
                    <p>A Healthier Mind for a Brighter Future.</p>
                    <button class="btn" onclick="window.location.href='Student/index.php'">Start Now!</button>
                </div>
                <img src="image/features/1.png" alt="">
            </div>
        </div>
        <div class="about" id="about">
            <h1>ABOUT UNICARE</h1>
            <div class="about_unicare">
                <div class="img">
                    <img src="image/features/11.png" alt="">
                </div>
                <div class="description">
                    <p><span>Unicare</span> is a platform designed to support university students’ mental health and wellbeing. Our goal is to create a safe, caring space where students feel heard, and admins can provide timely guidance and support.</p>
                    <br>
                    <p>Students can use Unicare for daily wellbeing check-ins, access helpful resources, and track their mood over time. Admins can monitor overall student wellbeing trends and provide targeted support when needed.</p>
                </div>
            </div>
        </div>
        <div class="about" id="features">
            <h1>FEATURES OF UNICARE</h1>
            <div class="features">
                <div class="each_features">
                    <img src="image/features/2.png" alt="">
                    <div class="description">
                        <h3>Dashboard</h3>
                        <p>See how you’re feeling & track your vibes.</p>
                    </div>
                </div>
                <div class="each_features">
                    <img src="image/features/3.png" alt="">
                    <div class="description">
                        <h3>Book Of Answers</h3>
                        <p>Find quick tips & advice anytime.</p>
                    </div>
                </div>
                <div class="each_features">
                    <img src="image/features/5.png" alt="">
                    <div class="description">
                        <h3>Confession Posts</h3>
                        <p>Share your thoughts anonymously.</p>
                    </div>
                </div>
                <div class="each_features">
                    <img src="image/features/7.png" alt="">
                    <div class="description">
                        <h3>Leisure</h3>
                        <p>Chill with music & fun stuff.</p>
                    </div>
                </div>
                <div class="each_features">
                    <img src="image/features/8.png" alt="">
                    <div class="description">
                        <h3>Counselor Booking</h3>
                        <p>Book a chat with a counselor easily.</p>
                    </div>
                </div>
                <div class="each_features">
                    <img src="image/features/9.png" alt="">
                    <div class="description">
                        <h3>Unibot</h3>
                        <p>Best virtual buddy you need.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="about" id="contact">
            <h1>CONTACT US</h1>
            <div class="contact_wrapper">
                <div class="left_contact">
                    <img src="image/contact.png" alt="">
                </div>
                <div class="right_contact">
                    <form target="_blank" action="https://formsubmit.co/xuy4n2401@gmail.com" method="POST">
                        <div class="form">
                            <h3>GET IN TOUCH</h3>
                            <div class="input_form">
                                <input name="First Name" type="text" placeholder="First Name" title="First Name" required>
                                <input name="Last Name" type="text" placeholder="Last Name" title="Last Name" required>
                                <input name="Email"  type="text" placeholder="Email" title="Email" required>
                                <input name="Phone Number"  type="text" placeholder="Phone Number" title="Phone Number" required>
                                <textarea name="Message" placeholder="Enter your message here..." title="Your Message Here" required></textarea>
                                <button type="submit">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <footer>
        <div class="footer">
            <div class="footer_top">
                <div class="left">
                    <div class="name">
                        <img src="image/favicon.png" alt="">
                        <h1>Unicare</h1>
                    </div>
                    <p>A Healthier Mind for a Brighter Future.</p>
                </div>
                <div class="spacer"></div>
                <div class="right">
                    <!-- <div class="nav">
                        <a href="#"><img src=""><h1>Linkedin</h1></a>
                        <a href="#"><img src=""><h1>GitHub</h1></a>
                        <a href="#"><img src=""><h1>E-Mail</h1></a>
                        <a href="#"><img src=""><h1>WhatsApp</h1></a>
                    </div> -->
                </div>
            </div>
            <hr>
            <div class="cr">
                <p>&copy; <span id="year"></span> Unicare. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script>
        function toggleMenu() {
            const nav = document.getElementById("nav");
            nav.classList.toggle("show");
        }
        document.addEventListener('click', function(event) {
            const nav = document.getElementById("nav");
            const smallNav = document.querySelector('.small_nav img');
            
            if (
                nav.classList.contains('show') &&
                !nav.contains(event.target) &&
                !smallNav.contains(event.target)
            ) {
                nav.classList.remove('show');
            }
        });

        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>