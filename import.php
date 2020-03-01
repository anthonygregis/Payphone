<?php
$conn = mysqli_connect("localhost", "phoneadmin", "xq0H?2q1", "payphone");

$affectedRow = 0;

$xml = simplexml_load_file("input.xml") or die("Error: Cannot create object");

foreach ($xml->children() as $row) {
    $xCoord = $row->xcoord;
    $yCoord = $row->ycoord;
    
    $sql = "INSERT INTO phones(xcoord,ycoord) VALUES ('" . $xCoord . "','" . $yCoord . "')";
    
    $result = mysqli_query($conn, $sql);
    
    if (! empty($result)) {
        $affectedRow ++;
    } else {
        $error_message = mysqli_error($conn) . "\n";
    }
}
?>
<h2>Insert XML Data to MySql Table Output</h2>
<?php
if ($affectedRow > 0) {
    $message = $affectedRow . " records inserted";
} else {
    $message = "No records inserted";
}
    echo $message;

?>