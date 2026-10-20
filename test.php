<?php
$userName = "JohnDoe";
$englishScore = 85;
$mathScore = 90;
$scienceScore = 78;
$totalScore = $englishScore + $mathScore + $scienceScore;
$averageScore = $totalScore / 3;
echo "Student Name: " .$userName. "<br>";
echo "English Score: " .$englishScore. "<br>";
echo "Math Score: " .$mathScore. "<br>";
echo "Science Score: " .$scienceScore. "<br>";
echo "Total Score: " .$totalScore. "<br>";
echo "Average Score: " .$averageScore. "<br>";
if($averageScore >= 90) {
    echo "Grade: A";
} elseif($averageScore >= 80) {
    echo "Grade: B";
} elseif($averageScore >= 70) {
    echo "Grade: C";
} elseif($averageScore >= 60) {
    echo "Grade: D";
} else {
    echo "Grade: F";
}
?>