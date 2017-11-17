<?php
require 'src/config.php';
require 'src/facebook.php';

$facebook = new Facebook(array(
 'appId'  => $config['App_ID'],
 'secret' => $config['App_Secret'],
 'cookie' => true
));

if(isset($_GET['fbTrue']))
{
 $token_url = "https://graph.facebook.com/oauth/access_token?". "client_id=".$config['App_ID']."&redirect_uri=" . urlencode($config['callback_url']). "&client_secret=".$config['App_Secret']."&code=" . $_GET['code']; 
 $response = file_get_contents($token_url);
 $params = null;
 parse_str($response, $params);
 $graph_url = "https://graph.facebook.com/me?access_token=" .$params['access_token'];
 $user = json_decode(file_get_contents($graph_url));
 $content = $user;
}
else
{
 $content = '<a href="https://www.facebook.com/dialog/oauth?client_id='.$config['App_ID'].'&redirect_uri='.$config['callback_url'].'&scope=email,user_likes,publish_stream"><img src="./images/login-button.png" alt="Sign in with Facebook"/></a>';
}

include('html.inc');
?>

<html>
<body>

<?php
$config['callback_url'] = "http://www.your_site.com/index.php/?fbTrue=true";
?>

<a href="https://www.facebook.com/dialog/oauth?client_id='.$config['App_ID'].'&redirect_uri='.$config['callback_url'].'&scope=email,user_likes,publish_stream"><img src="./images/login-button.png" alt="Sign in with Facebook"/></a>

</body>
</html>
