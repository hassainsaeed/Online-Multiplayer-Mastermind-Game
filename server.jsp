<%@ page import="java.io.*" %>
<%@ page import="java.sql.*" %>
<%@ page import="java.util.Random" %>
<%
	//SERVER.JSP - BY HASSAIN SAEED 10085383
	
	String operation = request.getParameter("operation");
	
	//First lets try connecting to msql database - mastermind
	String dBuser = "root";
	String dbPassword = "123";
	
	response.setHeader("Access-Control-Allow-Origin", "*");
	
	try{
		Class.forName("com.mysql.jdbc.Driver");
		Connection conn = DriverManager.getConnection(
			"jdbc:mysql://localhost/mastermind", "root", "123");
			//connected to mysql database - mastermind
			
			if (operation.equals("1")) {
				//THIS OPERATION IS CALLED WHENEVER USER PRESSES JOINGAME BUTTON OR THE STARTGAME BUTTON
				//THIS JSP RECIEVES USERNAME AND SESSIONID (IF SET BY USER) AS INPUT
				//THIS JSP FILE THEN GENERATES RANDOM CODE FOR USER TO GUESS, CREATES A SESSION TEXT FILE [SessionID.txt], AND RETURNS THE SESSIONID AS AN OUTPUT
				String username = request.getParameter("username");
				String sessionID = request.getParameter("sessionID");
				int[] codeNums = new int[3];
				String[] codeColors = new String[3];
				
				for (int i = 0; i <3; i++) {
					
					codeNums[i] = (int) (Math.random() * 3);
					if (codeNums[i] == 0) {
						codeColors[i] = "B";
					} else if (codeNums[i] == 1) {
						codeColors[i] = "R";
					} else if (codeNums[i] == 2) {
						codeColors[i] = "G";
					}
				}
				
				//If no sessionID given by user, create a brand new one based on time in miliseconds
				if (sessionID == null) {
					long millis = System.currentTimeMillis()/1000;
					sessionID = Long.toString(millis);
					System.out.println("Time: " + sessionID);
					String sql =  "INSERT INTO games(game_id,code_to_guess,results) VALUES ('" + sessionID + "','" + codeColors[0] + ";" + codeColors[1] + ";" + codeColors[2] + "','')";
					PreparedStatement stmt = conn.prepareStatement(sql);
					stmt.executeUpdate(sql);
					out.print(sessionID);
				} else {
					//If user has  sent a sessionID in their XMLHTTPRequest, but theres not SessionID text file for it. Create new SessionID text file with randomly generated code
					System.out.println("Time: " + sessionID);
					
					String sql2 =  "SELECT * FROM games WHERE game_id= '" + sessionID + "'";
					PreparedStatement stmt = conn.prepareStatement(sql2);
					ResultSet resultSet = stmt.executeQuery(sql2);
					
					if (!resultSet.next()) {
							String sql =  "INSERT INTO games(game_id,code_to_guess,results) VALUES ('" + sessionID + "','" + codeColors[0] + ";" + codeColors[1] + ";" + codeColors[2] + "','')";
							PreparedStatement stmt2 = conn.prepareStatement(sql);
							stmt2.executeUpdate(sql);
					}
					out.print(sessionID);
				}
			} else if (operation.equals("2")) {
				//THIS OPERATION IS CALLED EVERYTIME USER GUESSES A COLOR CODE AND PRESSES 'OK'
				//THIS PHP FILE RECIEVES USERNAME, SESSIONID, AND THE 3 COLORS THE USER GUESSES AS INPUT
				//THIS PHP FILE THEN COMPARES THE USER GUESSES WITH THE RANDOM CODE IT GENERATED, COUNTS THE NUMBER OF CORRECT GUESSES BY USER, AND OUTPUTS IT TO A RESULTS TEXTFILE AND BACK TO THE HTML
				String[] userGuesses = new String[3];
				userGuesses[0] = request.getParameter("guess0");
				userGuesses[1] = request.getParameter("guess1");
				userGuesses[2] = request.getParameter("guess2");
				String username = request.getParameter("username");
				String sessionID = request.getParameter("sessionID");
				
				//Get the randomly generated code the Server created for this session from the SQL SERVER
				String sql = "SELECT code_to_guess FROM games WHERE game_id='"+ sessionID + "' "; 
				PreparedStatement stmt = conn.prepareStatement(sql);
				ResultSet resultSet = stmt.executeQuery(sql);
				
				String result = "";
				if (resultSet.next()) {
					result = resultSet.getString(1);
				} 
				
				//Make the random generated code into a string
				String codeToGuess[] = result.split(";");
				
				//Compare the Server generated Code with the User Guessed Code. For every element correct, NumCorrectGuesses increments by 1
				int numCorrectGuesses = 0;
				for (int i = 0; i <3; i++) {
					if (codeToGuess[i].equals(userGuesses[i])) {
						//out.print("User guess: " + userGuesses[i] + " Computer: " + codeToGuess[i]);
						numCorrectGuesses++;
					}
				}
				
				String message = "With " + userGuesses[0] + userGuesses[1] + userGuesses[2] + ", " + username + " has guessed " + numCorrectGuesses + " numbers correct. <br>\n";
				if (numCorrectGuesses == 3) {   //If user has all 3 guesses correct, They win!
					message = message + username + " has won the game. Game Over.";
				}
				//out.print(message);
				
				String sql2 = "UPDATE games SET results = CONCAT(results,'"+message+"') WHERE game_id='"+sessionID+"'";
				PreparedStatement stmt2 = conn.prepareStatement(sql2);
				stmt2.executeUpdate(sql2);
				
				String sql3 =  "SELECT results FROM games WHERE game_id='"+sessionID+"' ";
				PreparedStatement stmt3 = conn.prepareStatement(sql3);
				ResultSet resultSet3 = stmt3.executeQuery(sql3);
				if (resultSet3.next()) {
					String output = resultSet3.getString(1);
					out.print(output);
				} else {
					out.print("Error");
				}
				
			}	else if (operation.equals("3")) {
				//THIS OPERATION IS FIRST CALLED WHEN THE USER PRESSES THE JOINGAME OR STARTGAME BUTTON, AND THEN IT IS PERIODICALLY CALLED EVERY 5 SECONDS AFTER THAT
				//THIS JSP FILE RECIEVES THE SESSIONID AS THE INPUT
				//THIS JSP FILE THEN USES THE SESSIONID TO FIND THE CORRESPONDING RESULTS[SESSIONID].TXT FILE, AND IT OUTPUTS THE CONTENTS OF THAT TEXT FILE TO EVERY USER PLAYING
				
				String sessionID = request.getParameter("sessionID");
				String output = "";
				String sql = "SELECT results FROM games WHERE game_id='" + sessionID + "' ";
				PreparedStatement stmt = conn.prepareStatement(sql);
				ResultSet resultSet = stmt.executeQuery(sql);
				if (resultSet.next()) {
					output = resultSet.getString(1);
				} 
				
				out.print(output);
			}
				
	
	} 	catch(Exception e){
		out.print("error " + e.getMessage());
	}
	
	%>
	
