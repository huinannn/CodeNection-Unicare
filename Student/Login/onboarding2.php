<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Unicare</title>
        <link rel="icon" href="../../image/favicon.png" type="image/x-icon" />
        <link rel="stylesheet" href="login.css" />
    </head>
    <body>
        <div class="onboarding" data-page="onboarding2">
            <a href="login.php" class="skip">Skip</a>

            <div class="progress">
                <div class="progress-bar">
                    <div class="active"></div>
                    <div class="active"></div>
                    <div></div>
                </div>
                    <div class="progress-text">2/3</div>
            </div>

            <div class="title">Student Balance</div>

            <div class="subtitle">What area matters most to you right now?</div>

            <form class="options">
                <label class="option">
                    <input type="checkbox"/>
                    <span class="custom-box"></span>
                    📖 Coping with exam pressure
                </label>

                <label class="option">
                    <input type="checkbox"/>
                    <span class="custom-box"></span>
                    🕒 Managing time better
                </label>

                <label class="option">
                    <input type="checkbox"/>
                    <span class="custom-box"></span>
                    😴 Improving sleep & rest
                </label>

                <label class="option">
                    <input type="checkbox"/>
                    <span class="custom-box"></span>
                    🍎 Staying motivated & productive
                </label>

                <label class="option">
                    <input type="checkbox"/>
                    <span class="custom-box"></span>
                    ✨ Others
                </label>
            </form>

            <div class="btn-row">
                <a href="onboarding1.php" class="btn btn-1">Back</a>
                <a href="onboarding3.php" class="btn btn-2">Continue</a>
            </div>
        </div>
    </body>
    <script src="login.js"></script>
</html>