<?php
        class Account {
        private $con;
        private $errorArray;
        public function __construct($con) {
        $this->con = $con;
        $this->errorArray = array();
        }
        public function login($un, $pw) {
        $pw = md5($pw);
        $query = mysqli_query($this->con, "SELECT * FROM users WHERE username='$un'
        AND password='$pw'");
        if(mysqli_num_rows($query) == 1) {
            return true;
            }
            else {
            array_push($this->errorArray, Constants::$loginFailed);
            return false;
            }
            }
            public function register($un, $fn, $ln, $em, $em2, $pw, $pw2) {
            $this->validateUsername($un);
            $this->validateFirstName($fn);
            $this->validateLastName($ln);
            $this->validateEmails($em, $em2);
            $this->validatePasswords($pw, $pw2);
            if(empty($this->errorArray) == true) {
            //Insert into db
            return $this->insertUserDetails($un, $fn, $ln, $em, $pw);
            }
            else {
            return false;
            }
            }
            public function getError($error) {
            if(!in_array($error, $this->errorArray)) {
            $error = "";
            }
            return "<span class='errorMessage'>$error</span>";
            }
            private function insertUserDetails($un, $fn, $ln, $em, $pw) {
            $encryptedPw = md5($pw);
            $profilePic = "assets/images/profile-pics/head_emerald.png";
            $date = date("Y-m-d");
            $result = mysqli_query($this->con, "INSERT INTO users VALUES ('', '$un', '$fn', '$ln',
            '$em', '$encryptedPw', '$date', '$profilePic')");
            return $result;
            }
            private function validateUsername($un) {
            if(strlen($un) > 25 || strlen($un) < 5) {
            array_push($this->errorArray, Constants::$usernameCharacters);
            return;
            }
            $checkUsernameQuery = mysqli_query($this->con, "SELECT username FROM users WHERE username='$un'");
            if(mysqli_num_rows($checkUsernameQuery) != 0) {
            array_push($this->errorArray, Constants::$usernameTaken);
            return;
            }
            }
            private function validateFirstName($fn) {
            if(strlen($fn) > 25 || strlen($fn) < 2) {
            array_push($this->errorArray, Constants::$firstNameCharacters);
            return;
            }
            }
            private function validateLastName($ln) {
            if(strlen($ln) > 25 || strlen($ln) < 2) {
            array_push($this->errorArray, Constants::$lastNameCharacters);
            return;
            }
            }
            private function validateEmails($em, $em2) {
            if($em != $em2) {
            array_push($this->errorArray, Constants::$emailsDoNotMatch);
            return;
            }
            if(!filter_var($em, FILTER_VALIDATE_EMAIL)) {
            array_push($this->errorArray, Constants::$emailInvalid);
            return;
            }
            $checkEmailQuery = mysqli_query($this->con, "SELECT email FROM users WHERE
            email='$em'");
            if(mysqli_num_rows($checkEmailQuery) != 0) {
            array_push($this->errorArray, Constants::$emailTaken);
            return;
            }
            }
            private function validatePasswords($pw, $pw2) {
            if($pw != $pw2) {
            array_push($this->errorArray, Constants::$passwordsDoNoMatch);
            return;
            }
            if(preg_match('/[^A-Za-z0-9]/', $pw)) {
            array_push($this->errorArray, Constants::$passwordNotAlphanumeric);
            return;
            }
            if(strlen($pw) > 30 || strlen($pw) < 5) {
                array_push($this->errorArray, Constants::$passwordCharacters);
                return;
                }
                }
                }
        ?>

<?php
class Constants {
public static $passwordsDoNoMatch = "Your passwords don't match";
public static $passwordNotAlphanumeric = "Your password can only contain numbers and letters";
public static $passwordCharacters = "Your password must be between 5 and 30 characters";
public static $emailInvalid = "Email is invalid";
public static $emailsDoNotMatch = "Your emails don't match";
public static $emailTaken = "This email is already in use";
public static $lastNameCharacters = "Your last name must be between 2 and 25 characters";
public static $firstNameCharacters = "Your first name must be between 2 and 25 characters";
public static $usernameCharacters = "Your username must be between 5 and 25 characters";
public static $usernameTaken = "This username already exists";
public static $loginFailed = "Your username or password was incorrect";
}
?>
<?php
if(isset($_POST['loginButton'])) {
 //Login button was pressed
 $username = $_POST['loginUsername'];
 $password = $_POST['loginPassword'];
 $result = $account->login($username, $password);
 if($result == true) {
 $_SESSION['userLoggedIn'] = $username;
 header("Location: index.php");
 }
}
?>
<?php
function sanitizeFormPassword($inputText) {
$inputText = strip_tags($inputText);
return $inputText;
}
function sanitizeFormUsername($inputText) {
    $inputText = strip_tags($inputText);
$inputText = str_replace(" ", "", $inputText);
return $inputText;
}
function sanitizeFormString($inputText) {
$inputText = strip_tags($inputText);
$inputText = str_replace(" ", "", $inputText);
$inputText = ucfirst(strtolower($inputText));
return $inputText;
}
if(isset($_POST['registerButton'])) {
//Register button was pressed
$username = sanitizeFormUsername($_POST['username']);
$firstName = sanitizeFormString($_POST['firstName']);
$lastName = sanitizeFormString($_POST['lastName']);
$email = sanitizeFormString($_POST['email']);
$email2 = sanitizeFormString($_POST['email2']);
$password = sanitizeFormPassword($_POST['password']);
$password2 = sanitizeFormPassword($_POST['password2']);
$wasSuccessful = $account->register($username, $firstName, $lastName, $email, $email2, $password,
$password2);
if($wasSuccessful == true) {
$_SESSION['userLoggedIn'] = $username;
header("Location: index.php");
}
}
?>
<?php
ob_start();
session_start();
$timezone = date_default_timezone_set("Europe/London");
$con = mysqli_connect("localhost", "root", "", "slotify");
if(mysqli_connect_errno()) {
echo "Failed to connect: " . mysqli_connect_errno();
}
?>
<?php
include("includes/config.php");
//session_destroy(); LOGOUT
if(isset($_SESSION['userLoggedIn'])) {
$userLoggedIn = $_SESSION['userLoggedIn'];
}
else {
    header("Location: register.php");
    }
    ?>
    <html>
    <head>
    <title>Welcome to Slotify!</title>
    </head>
    <body>
    Hello!
    </body>
    </html>
    <?php
    include("includes/config.php");
    include("includes/classes/Account.php");
    include("includes/classes/Constants.php");
    $account = new Account($con);
    include("includes/handlers/register-handler.php");
    include("includes/handlers/login-handler.php");
    function getInputValue($name) {
    if(isset($_POST[$name])) {
    echo $_POST[$name];
    }
    }
    ?>
<?php include("includes/header.php");
if(isset($_GET['id'])) {
$albumId = $_GET['id'];
}
else {
header("Location: index.php");
}
$album = new Album($con, $albumId);
$artist = $album->getArtist();
?>
<div class="entityInfo">
<div class="leftSection">
<img src="<?php echo $album->getArtworkPath(); ?>">
</div>
<div class="rightSection">
<h2><?php echo $album->getTitle(); ?></h2>
<p>By <?php echo $artist->getName(); ?></p>
<p><?php echo $album->getNumberOfSongs(); ?> songs</p>
</div>
</div>
<div class="tracklistContainer">
<ul class="tracklist">
<?php
$songIdArray = $album->getSongIds();
$i = 1;
foreach($songIdArray as $songId) {
$albumSong = new Song($con, $songId);
$albumArtist = $albumSong->getArtist();
echo "<li class='tracklistRow'>
<div class='trackCount'>
<img class='play' src='assets/images/icons/play-white.png'
onclick='setTrack(\"" . $albumSong->getId() . "\", tempPlaylist, true)'>
<span class='trackNumber'>$i</span>
</div>
<div class='trackInfo'>
<span class='trackName'>" . $albumSong->getTitle() . "</span>
<span class='artistName'>" . $albumArtist->getName() .
"</span>
</div>
<div class='trackOptions'>
<img class='optionsButton' src='assets/images/icons/more.png'>
</div>
<div class='trackDuration'>
<span class='duration'>" . $albumSong->getDuration() .
"</span>
</div>
</li>";
$i = $i + 1;
}
?>
<script>
var tempSongIds = '<?php echo json_encode($songIdArray); ?>';
tempPlaylist = JSON.parse(tempSongIds);
</script>
</ul>
</div>
<?php include("includes/footer.php"); ?>
<?php include("includes/header.php"); ?>
<h1 class="pageHeadingBig">You Might Also Like</h1>
<div class="gridViewContainer">
<?php
$albumQuery = mysqli_query($con, "SELECT * FROM albums ORDER BY RAND() LIMIT
10");
while($row = mysqli_fetch_array($albumQuery)) {
echo "<div class='gridViewItem'>
<a href='album.php?id=" . $row['id'] . "'>
<img src='" . $row['artworkPath'] . "'>
<div class='gridViewInfo'>"
. $row['title'] .
"</div>
</a>
</div>";
}
?>
</div>
<?php include("includes/footer.php"); ?>
<?php
include("includes/config.php");
include("includes/classes/Account.php");
include("includes/classes/Constants.php");
$account = new Account($con);
include("includes/handlers/register-handler.php");
include("includes/handlers/login-handler.php");
function getInputValue($name) {
    if(isset($_POST[$name])) {
        echo $_POST[$name];
        }
        }
        ?>
<?php include("includes/includedFiles.php");
if(isset($_GET['id'])) {
 $playlistId = $_GET['id'];
}
else {
 header("Location: index.php");
}
$playlist = new Playlist($con, $playlistId);
$owner = new User($con, $playlist->getOwner());
?>

<div class="entityInfo">
 <div class="leftSection">
 <div class="playlistImage">
 <img src="assets/images/icons/playlist.png">
 </div>
 </div>
 <div class="rightSection">
 <h2><?php echo $playlist->getName(); ?></h2>
 <p>By <?php echo $playlist->getOwner(); ?></p>
 <p><?php echo $playlist->getNumberOfSongs(); ?> songs</p>
 <button class="button" onclick="deletePlaylist('<?php echo $playlistId;
?>')">DELETE PLAYLIST</button>
 </div>
</div>
<div class="tracklistContainer">
 <ul class="tracklist">

 <?php
 $songIdArray = $playlist->getSongIds();
 $i = 1;
 foreach($songIdArray as $songId) {
 $playlistSong = new Song($con, $songId);
 $songArtist = $playlistSong->getArtist();
 echo "<li class='tracklistRow'>
 <div class='trackCount'>
 <img class='play' src='assets/images/icons/play-white.png'
onclick='setTrack(\"" . $playlistSong->getId() . "\", tempPlaylist, true)'>
 <span class='trackNumber'>$i</span>
 </div>
 <div class='trackInfo'>
 <span class='trackName'>" . $playlistSong->getTitle() .
"</span>
 <span class='artistName'>" . $songArtist->getName() . "</span>
 </div>
 <div class='trackOptions'>
 <input type='hidden' class='songId' value='" . $playlistSong-
>getId() . "'>
 <img class='optionsButton' src='assets/images/icons/more.png'
onclick='showOptionsMenu(this)'>
 </div>
 <div class='trackDuration'>
 <span class='duration'>" . $playlistSong->getDuration() .
"</span>
</div>
 </li>";
 $i = $i + 1;
 }
 ?>
 <script>
 var tempSongIds = '<?php echo json_encode($songIdArray); ?>';
 tempPlaylist = JSON.parse(tempSongIds);
 </script>
 </ul>
</div>
<nav class="optionsMenu">
 <input type="hidden" class="songId">
 <?php echo Playlist::getPlaylistsDropdown($con, $userLoggedIn->getUsername()); ?>
 <div class="item" onclick="removeFromPlaylist(this, '<?php echo $playlistId;
?>')">Remove from Playlist</div>
</nav>
<?php
include("includes/includedFiles.php");
if(isset($_GET['term'])) {
$term = urldecode($_GET['term']);
}
else {
$term = "";
}
?>
<div class="searchContainer">
<h4>Search for an artist, album or song</h4>
<input type="text" class="searchInput" value="<?php echo $term; ?>" placeholder="Start typing..."
onfocus="this.value = this.value">
</div>
<script>
$(".searchInput").focus();
$(function() {
$(".searchInput").keyup(function() {
clearTimeout(timer);
timer = setTimeout(function() {
var val = $(".searchInput").val();
openPage("search.php?term=" + val);
}, 2000);
})
})
</script>
<?php if($term == "") exit(); ?>
<div class="tracklistContainer borderBottom">
<h2>SONGS</h2>
<ul class="tracklist">
<?php
$songsQuery = mysqli_query($con, "SELECT id FROM songs WHERE title LIKE '$term%'
LIMIT 10");
if(mysqli_num_rows($songsQuery) == 0) {
echo "<span class='noResults'>No songs found matching " . $term . "</span>";
}
$songIdArray = array();
$i = 1;
while($row = mysqli_fetch_array($songsQuery)) {
if($i > 15) {
break;
}
array_push($songIdArray, $row['id']);
$albumSong = new Song($con, $row['id']);
$albumArtist = $albumSong->getArtist();
echo "<li class='tracklistRow'>
<div class='trackCount'>
<img class='play' src='assets/images/icons/play-white.png'
onclick='setTrack(\"" . $albumSong->getId() . "\", tempPlaylist, true)'>
<span class='trackNumber'>$i</span>
</div>
<div class='trackInfo'>
<span class='trackName'>" . $albumSong->getTitle() . "</span>
<span class='artistName'>" . $albumArtist->getName() . 
"</span>
</div>
<div class='trackOptions'>
<input type='hidden' class='songId' value='" . $albumSong-
>getId() . "'>
<img class='optionsButton' src='assets/images/icons/more.png'
onclick='showOptionsMenu(this)'>
</div>
<div class='trackDuration'>
<span class='duration'>" . $albumSong->getDuration() .
"</span>
</div>
</li>";
$i = $i + 1;
}
?>
<script>
var tempSongIds = '<?php echo json_encode($songIdArray); ?>';
tempPlaylist = JSON.parse(tempSongIds);
</script>
</ul>
</div>
<div class="artistsContainer borderBottom">
<h2>ARTISTS</h2>
<?php
$artistsQuery = mysqli_query($con, "SELECT id FROM artists WHERE name LIKE '$term%' LIMIT
10");
if(mysqli_num_rows($artistsQuery) == 0) {
echo "<span class='noResults'>No artists found matching " . $term . "</span>";
}
while($row = mysqli_fetch_array($artistsQuery)) {
$artistFound = new Artist($con, $row['id']);
echo "<div class='searchResultRow'>
<div class='artistName'>
<span role='link' tabindex='0' onclick='openPage(\"artist.php?id=" .
$artistFound->getId() ."\")'>
"
. $artistFound->getName() .
"
</span>
</div>
</div>";
}
?>
</div>
<div class="gridViewContainer">
<h2>ALBUMS</h2>
<?php
$albumQuery = mysqli_query($con, "SELECT * FROM albums WHERE title LIKE '$term%'
LIMIT 10");
if(mysqli_num_rows($albumQuery) == 0) {
echo "<span class='noResults'>No albums found matching " . $term . "</span>";
}
while($row = mysqli_fetch_array($albumQuery)) {
echo "<div class='gridViewItem'>
<span role='link' tabindex='0' onclick='openPage(\"album.php?id=" .
$row['id'] . "\")'>
<img src='" . $row['artworkPath'] . "'>
<div class='gridViewInfo'>"
. $row['title'] .
"</div>
</span>
</div>";
}
?>
</div>
<nav class="optionsMenu">
<input type="hidden" class="songId">
<?php echo Playlist::getPlaylistsDropdown($con, $userLoggedIn->getUsername()); ?>
</nav>
<?php
include("includes/includedFiles.php");
?>
<div class="entityInfo">
<div class="centerSection">
<div class="userInfo">
<h1><?php echo $userLoggedIn->getFirstAndLastName(); ?></h1>
</div>
</div>
<div class="buttonItems">
<button class="button" onclick="openPage('updateDetails.php')">USER DETAILS</button>
<button class="button" onclick="logout()">LOGOUT</button>
</div>
</div>
<?php
include("includes/includedFiles.php");
?>
<div class="userDetails">
 <div class="container borderBottom">
 <h2>EMAIL</h2>
 <input type="text" class="email" name="email" placeholder="Email address..." value="<?php echo
$userLoggedIn->getEmail(); ?>">
 <span class="message"></span>
 <button class="button" onclick="updateEmail('email')">SAVE</button>
 </div>
 <div class="container">
 <h2>PASSWORD</h2>
 <input type="password" class="oldPassword" name="oldPassword" placeholder="Current password">
 <input type="password" class="newPassword1" name="newPassword1" placeholder="New password">
 <input type="password" class="newPassword2" name="newPassword2" placeholder="Confirm password">
 <span class="message"></span>
 <button class="button" onclick="updatePassword('oldPassword', 'newPassword1',
'newPassword2')">SAVE</button>
 </div>
</div>
<?php
include("includes/includedFiles.php");
?>
<div class="playlistsContainer">
<div class="gridViewContainer">
<h2>PLAYLISTS</h2>
<div class="buttonItems">
<button class="button green" onclick="createPlaylist()">NEW PLAYLIST</button>
</div>
<?php
$username = $userLoggedIn->getUsername();
$playlistsQuery = mysqli_query($con, "SELECT * FROM playlists WHERE
owner='$username'");
if(mysqli_num_rows($playlistsQuery) == 0) {
echo "<span class='noResults'>You don't have any playlists yet.</span>";
}
while($row = mysqli_fetch_array($playlistsQuery)) {
$playlist = new Playlist($con, $row);
echo "<div class='gridViewItem' role='link' tabindex='0'
onclick='openPage(\"playlist.php?id=" . $playlist-
>getId() . "\")'>
<div class='playlistImage'>
<img src='assets/images/icons/playlist.png'>
</div>
<div class='gridViewInfo'>"
. $playlist->getName() .
"</div>
</div>";
}
?>
</div>
</div>
<?php
include("includes/includedFiles.php");
?>
<h1 class="pageHeadingBig">You Might Also Like</h1>
<div class="gridViewContainer">
<?php
$albumQuery = mysqli_query($con, "SELECT * FROM albums ORDER BY RAND() LIMIT
10");
while($row = mysqli_fetch_array($albumQuery)) {
echo "<div class='gridViewItem'>
<span role='link' tabindex='0' onclick='openPage(\"album.php?id=" .
$row['id'] . "\")'>
<img src='" . $row['artworkPath'] . "'>
<div class='gridViewInfo'>"
. $row['title'] .
"</div>
</span>
</div>";
}
?>
</div>
