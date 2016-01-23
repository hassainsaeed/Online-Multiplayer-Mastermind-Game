<?php
//SERVER.PHP - BY HASSAIN SAEED 100853383
$operation=$_GET['operation'];
$dBhost = 'localhost';
$dBpassword = '123';
$dBusername = 'root';
$dBname='mastermind';

$mysqli = new mysqli($dBhost,$dBusername,$dBpassword,$dBname);

if ($mysqli->connect_error) {
	die("Connection failed".$mysqli->connect_error);
}

header("Access-Control-Allow-Origin: *");
	

if ($operation == 1) {
	//THIS OPERATION IS CALLED WHENEVER USER PRESSES JOINGAME BUTTON OR THE STARTGAME BUTTON
	//THIS PHP FILE RECIEVES USERNAME AND SESSIONID (IF SET BY USER) AS INPUT
	//THIS PHP FILE THEN GENERATES RANDOM CODE FOR USER TO GUESS, CREATES A SESSION TEXT FILE [SessionID.txt], AND RETURNS THE SESSIONID AS AN OUTPUT
	$username = $_GET['username'];	
	$codeNums = array();
	$codeColors = array();	

	//Generate the random code of 3 colors of Blue Red and Green 
	for($x = 0; $x < 3; $x++) {
    	$codeNums[$x] = rand(0,2);
			if ($codeNums[$x] == 0) {
				$codeColors[$x] = "B";
			} else if ($codeNums[$x] == 1) {
				$codeColors[$x] = "R";
			} else if ($codeNums[$x] == 2) {
				$codeColors[$x] = "G";
			}
		}
	
	//If user has not sent a sessionID in their XMLHTTPRequest, create new SessionID based on time in ms. Create SessionID text file with randomly generated code
	if  (!isset($_GET['sessionID'])) {
		$sessionID = time();
		//file_put_contents("".$sessionID.".txt","".$codeColors[0].";".$codeColors[1].";".$codeColors[2]."");
		$sql = "INSERT INTO games(game_id,code_to_guess,results) VALUES ('".$sessionID."','".$codeColors[0].";".$codeColors[1].";".$codeColors[2]."','')";
		if (mysqli_query($mysqli,$sql)) {
			echo "".$sessionID;
		} else {
			echo "Error: ".$sql."<br>".mysqli_error($mysqli);
		}
	} else {
		$sessionID = $_GET['sessionID'];
		
		//If user has  sent a sessionID in their XMLHTTPRequest, but theres not SessionID text file for it. Create new SessionID text file with randomly generated code
		$sql2 = "SELECT * FROM games WHERE game_id='".$sessionID."' ";
		$resultQuery2 = $mysqli->query($sql2);
		if ($resultQuery2->num_rows == 0) {	
			//file_put_contents("".$sessionID.".txt","".$codeColors[0].";".$codeColors[1].";".$codeColors[2]."");
			$sql = "INSERT INTO games(game_id,code_to_guess,results) VALUES ('".$sessionID."','".$codeColors[0].";".$codeColors[1].";".$codeColors[2]."','')";
			if (mysqli_query($mysqli,$sql)) {
				echo "".$sessionID;
			} else {
				echo "Error: ".$sql."<br>".mysqli_error($mysqli);
			}
		} else {
			echo "".$sessionID;
		}
	}
	
	//Set cookies for the Username, SessionID, and Randomly Generated Code
	setCookie("user", $username);
	setCookie("sessionID", $sessionID);
	setCookie("codeColor0",$codeColors[0]);
	setCookie("codeColor1",$codeColors[1]);
	setCookie("codeColor2",$codeColors[2]);
	//echo "".$sessionID;     //Output/return the sessionID back to user via XMLHTTPRequest
	//echo ";".$codeColors[0].";".$codeColors[1].";".$codeColors[2]; 
} else if ($operation == 2) {
	
		//THIS FILE IS CALLED EVERYTIME USER GUESSES A COLOR CODE AND PRESSES 'OK'
	//THIS PHP FILE RECIEVES USERNAME, SESSIONID, AND THE 3 COLORS THE USER GUESSES AS INPUT
	//THIS PHP FILE THEN COMPARES THE USER GUESSES WITH THE RANDOM CODE IT GENERATED, COUNTS THE NUMBER OF CORRECT GUESSES BY USER, AND OUTPUTS IT TO A RESULTS TEXTFILE AND BACK TO THE HTML
	$userGuesses = array();
	$userGuesses[0] = $_GET['guess0'];
	$userGuesses[1] = $_GET['guess1'];
	$userGuesses[2] = $_GET['guess2'];
	$sessionID = $_GET['sessionID'];
	$username = $_GET['username'];

	//Get the randomly generated code the Server created for this session from the [sessionID].txt file
	//$code = file_get_contents($sessionID.".txt"); 
	$sql = "SELECT code_to_guess FROM games WHERE game_id='".$sessionID."' ";
//	if (mysqli_query($mysqli,$sql)) {
	$resultQuery = $mysqli->query($sql);
	if ($resultQuery == true) {
		$row = $resultQuery->fetch_object();
		$code = $row->code_to_guess;
		//echo "".$code;
	} else {
		echo "Error: ".$sql."<br>".mysqli_error($mysqli);
	}	
	$codeColor = array();
	$CodeColor = explode(";",$code);                 //Remove the semi-colon character from the randomly generated code, and save the results into an array called CodeColor
	
	$numCorrectGuesses = 0;

	//Compare the Server generated Code with the User Guessed Code. For every element correct, NumCorrectGuesses increments by 1
	for($x = 0; $x < 3; $x++) {
		if (strcmp($CodeColor[$x],$userGuesses[$x]) == 0) {
			$numCorrectGuesses++;
		}
	}
	
	//Output how many Correct guesses the user has made
	$message = "With ".$userGuesses[0].$userGuesses[1].$userGuesses[2].", ".$username." has guessed ".$numCorrectGuesses." numbers correct. <br>\n";
	if ($numCorrectGuesses == 3) {   //If user has all 3 guesses correct, They win!
		$message = $message.$username." has won the game. Game Over.";
	}
	//file_put_contents("result".$sessionID.".txt",$message, FILE_APPEND|LOCK_EX);      //Put the users results into a text file called result[SessionID].txt
	$sql = "UPDATE games SET results = CONCAT(results,'".$message."') WHERE game_id='".$sessionID."'";
	if (mysqli_query($mysqli,$sql)) {
		//$output = file_get_contents("result".$sessionID.".txt",true);
		$sql2 = "SELECT results FROM games WHERE game_id='".$sessionID."' ";
		$resultQuery2 = $mysqli->query($sql2);
		if ($resultQuery2 == true) {
			$row2 = $resultQuery2->fetch_object();
			$result = $row2->results;
			echo "".$result;
		} else {
			echo "Error: ".$sql2."<br>".mysqli_error($mysqli);
		}
	} else {
			echo "Error: ".$sql."<br>".mysqli_error($mysqli);
	}
	//$output = file_get_contents("result".$sessionID.".txt",true);                                     //Output the entirety of the result[SessionID].txt back to user via XMLHTTPRequest
	//echo $message;
	
} else if ($operation == 3) {
		//THIS FILE IS FIRST CALLED WHEN THE USER PRESSES THE JOINGAME OR STARTGAME BUTTON, AND THEN IT IS PERIODICALLY CALLED EVERY 5 SECONDS AFTER THAT
	//THIS PHP FILE RECIEVES THE SESSIONID AS THE INPUT
	//THIS PHP FILE THEN USES THE SESSIONID TO FIND THE CORRESPONDING RESULTS[SESSIONID].TXT FILE, AND IT OUTPUTS THE CONTENTS OF THAT TEXT FILE TO EVERY USER PLAYING
	$sessionID = $_GET['sessionID'];	
	
		//$output = file_get_contents("result".$sessionID.".txt",true);
		$sql = "SELECT results FROM games WHERE game_id='".$sessionID."' ";
//	if (mysqli_query($mysqli,$sql)) {
	$resultQuery = $mysqli->query($sql);
	if ($resultQuery == true) {
		if ($resultQuery->num_rows == 0) {
			echo "";
		} else {
			$row = $resultQuery->fetch_object();
			$result = $row->results;
			echo "".$result;
		}
	} else {
		echo "Error: ".$sql."<br>".mysqli_error($mysqli);
	}
		//echo $output;     //If the results[sessioniD].txt file does exist for this session - then output the contents of this text file to all users currently playing automatically   
	
}
	

?>
