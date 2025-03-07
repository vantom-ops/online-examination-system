<?php 
    include('check.php');

    // Start session only if not already active
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Ensure totalScore is set
    $totalScore = $_SESSION['totalScore'] ?? 0; 

    // Ensure student username is set
    if (!isset($_SESSION['student'])) {
        die("Error: Student session not set.");
    }

    $tbl_name = "tbl_student";
    $username = $_SESSION['student'];
    
    // Get student ID safely
    $student_id = $obj->get_userid($tbl_name, $username, $conn);
    if (!$student_id) {
        die("Error: Student ID not found.");
    }

    // Insert result summary
    $added_date = date('Y-m-d');
    $tbl_name2 = "tbl_result_summary";
    $data = "student_id='$student_id',
            marks='$totalScore',
            added_date='$added_date'";
    
    $query = $obj->insert_data($tbl_name2, $data);
    $res = $obj->execute_query($conn, $query);
    if (!$res) {
        die("Error inserting data into result summary.");
    }
?>
<!--Body Starts Here-->
<div class="main">
    <div class="content">
        <div class="welcome">
            <?php 
                if (isset($_SESSION['time_complete'])) {
                    echo $_SESSION['time_complete'];
                }
            ?>
            You have successfully completed the test. Thank You.<br />

            <?php 
                // Get Student ID
                $tbl_name = 'tbl_student';
                $userid = $obj->get_userid($tbl_name, $username, $conn);
                
                // Get latest summary result from DB
                $tbl_name3 = "tbl_result_summary";
                $where3 = "student_id=$userid ORDER BY summary_id DESC LIMIT 1";
                $query = $obj->select_data($tbl_name3, $where3);
                $res = $obj->execute_query($conn, $query);

                // Ensure query returned data
                if (!$res) {
                    die("Error: Query execution failed.");
                }
                if (mysqli_num_rows($res) == 0) {
                    die("Error: No result data found.");
                }

                $row = $obj->fetch_data($res);
                $marks = $row['marks'];

                // Ensure full_marks is set to avoid division by zero
                $obtainedMarks = $_SESSION['totalScore'] ?? 0;
                $full_marks = $_SESSION['full_marks'] ?? 1; // Default to 1 to prevent division by zero

                // Calculate percentage safely
                $obtainedPercent = ($obtainedMarks / $full_marks) * 100;

                // Adjust marks based on faculty
                $marksShown = $obtainedMarks; // Default

                if (isset($_SESSION['facultyName'])) {
                    if ($_SESSION['facultyName'] == 'GRE') {
                        $marksShown = 260 + round($obtainedPercent * 0.8);
                    } elseif ($_SESSION['facultyName'] == 'GMAT') {
                        $marksShown = 200 + round($obtainedPercent * 6);
                    }
                }

                $_SESSION['USERID'] = $userid;

                // Round off marks safely
                $lastDigit = substr((string) intval($marksShown), -1);
                if ($lastDigit < 5) {
                    $realMark = $marksShown - $lastDigit;
                } else {
                    $realMark = $marksShown + (10 - $lastDigit);
                }
            ?>
            You got <h2><?php echo $realMark; ?></h2>
            
            <a href="<?php echo SITEURL; ?>index.php?page=detail_result">
                <button type="button" class="btn-exit">View Result</button>
            </a>

            <a href="<?php echo SITEURL; ?>index.php?page=logout">
                <button type="button" class="btn-exit">&nbsp; Log Out &nbsp;</button>
            </a>
        </div>
    </div>
</div>
<!--Body Ends Here-->
