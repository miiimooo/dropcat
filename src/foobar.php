<?php
$input = file_get_contents("php://input");
$myFile = "temp_file.txt";

if (0 === strpos($input, 'alert=')) {
  $output = preg_replace('/^alert=/', '', $input);
  $output = urldecode($output);
  $output = json_decode($output);
  $app = "$output->{'application_name'}";
  $data = '{"text":' . '"' . $app . '"}';
  //$message = $output->{'application_name'};
  file_put_contents($myFile, $app));
}
echo '{ "success": true }';

$ch = curl_init('https://wkchat.wklive.net/hooks/JMTJyEPCzxYoYPhDo/ne3Ldy2TdkMxG7zwExSfqibDoPajq4gx4Ma52Qyf3XkXhG4E');
// set URL and other appropriate options
$options = array(
  CURLOPT_HEADER => false,
  CURLOPT_HTTPHEADER => array(
	"Content-Type: application/json",
	"Accept: application/json",
  ),
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => $data,
);
curl_setopt_array($ch, $options);
curl_exec($ch); // grab URL and pass it to the browser
curl_close($ch);



$message = $_GET['message'];

$data = '{"text":' . '"' . $message . '"}';


//$ch = curl_init('https://rocketchat.wklive.net/hooks/FtW3dNw2fEaQpym4W/rocket.cat/Nf0RMGpeRMqGyqBBCW6JuGszeadpaOcX%2Fwp2upN31qk%3D');
$ch = curl_init('https://wkchat.wklive.net/hooks/zFWoraEHhuZuxZKQA/rocket.cat/NgGV6aRK4d4BsyxauPeIkeH%2Bh8EnjIQBx7fybfOHvmM%3D');
// set URL and other appropriate options
$options = array(
  CURLOPT_HEADER => false,
  CURLOPT_HTTPHEADER => array(
	"Content-Type: application/json",
	"Accept: application/json",
  ),
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => $data,
);
curl_setopt_array($ch, $options);
curl_exec($ch); // grab URL and pass it to the browser
curl_close($ch);
