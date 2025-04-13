<?php
session_start();
include("includes/config.php");

// यूजर लॉगिन चेक
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// रैंडम कैप्चा फेच करें
$sql = "SELECT * FROM captchas WHERE is_solved = FALSE ORDER BY RAND() LIMIT 1";
$result = $conn->query($sql);
$captcha = $result->fetch_assoc();
?>

<!-- कैप्चा दिखाएँ -->
<img src="<?php echo $captcha['image_path']; ?>" alt="Captcha">
<form method="POST" action="">
    <input type="text" name="user_answer" placeholder="Enter Captcha" required>
    <input type="hidden" name="captcha_id" value="<?php echo $captcha['id']; ?>">
    <button type="submit" name="submit">Submit</button>
</form>

<?php
if (isset($_POST['submit'])) {
    $user_answer = $_POST['user_answer'];
    $captcha_id = $_POST['captcha_id'];

    // सही जवाब चेक करें
    $sql = "SELECT * FROM captchas WHERE id = $captcha_id";
    $result = $conn->query($sql);
    $captcha = $result->fetch_assoc();

    if (strtolower($user_answer) == strtolower($captcha['solution'])) {
        // बैलेंस अपडेट करें (₹1 प्रति कैप्चा)
        $user_id = $_SESSION['user_id'];
        $conn->query("UPDATE users SET balance = balance + 1 WHERE id = $user_id");
        $conn->query("UPDATE captchas SET is_solved = TRUE, user_id = $user_id WHERE id = $captcha_id");
        echo "Correct! ₹1 Added to your balance.";
    } else {
        echo "Wrong Answer! Try Again.";
    }
}
?>
