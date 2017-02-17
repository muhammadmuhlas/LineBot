<?php

use \LINE\LINEBot\SignatureValidator as SignatureValidator;

class Response
{

	public $bot;
	public $request;

	function __construct(){

		$this->request = file_get_contents('php://input');

		/* Get Header Data */
		$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'];

		/* Logging to Console*/
		file_put_contents('php://stderr', 'Body: '.$this->request);

		/* Validation */
		if (empty($signature)){
			return $response->withStatus(400, 'Signature not set');
		}

		if($_ENV['PASS_SIGNATURE'] == false && ! SignatureValidator::validateSignature($this->request, $_ENV['CHANNEL_SECRET'], $signature)){
			return $response->withStatus(400, 'Invalid signature');
		}

		/* Initialize bot*/
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
		$this->bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);

	}

	public function getDisplayName($userId = null){

		$getProfile = $this->bot->getProfile();
		$profile = json_decode($getProfile, true);
		$displayName = $profile['displayName'];
	}

	public function eventsHandler(){

		$getRequest = json_decode($this->request, true);

		foreach ($getRequest['events'] as $event){

			if(strpos($event['message']['text'], 'bye dwabot') !== false){

				switch ($event['source']['type']) {

					case 'room':
						$response = $this->bot->leaveRoom($event['source']['roomId']);
						return $response->getHTTPStatus() . ' ' . $response->getRawBody();
						break;

					case 'group':
						$response = $this->bot->leaveGroup($event['source']['groupId']);
						return $response->getHTTPStatus() . ' ' . $response->getRawBody();
						break;

					default:
						echo "Undefined Source Type";
						break;
				}
			}

			if (strpos($event['message']['text'], 'about dwabot') !== false){

				$text = "
				Dirty Word Alert Bot will scan any dirty word and its possible combination. Put me in your group or multichat and I will do the job.

				DWABot did not alert some 'word' or didn't work well? Please click 'Suggest Word & Bug Report'
				Have an idea for future DWABot feature? please click 'Suggest Feature'
				*via personal

				--DWABot v0.2-alpha--
				======= K L M =======
				Kurniawan Eka Nugraha
				Lantang Satriatama
				Muhammad Muhlas Abror
				";

				$response = $this->bot->replyText($event['replyToken'], $text);
            	return $response->getHTTPStatus() . ' ' . $response->getRawBody();
			}

			switch ($event['type']) {

				case 'message':

					if($event['message']['type'] == 'text'){

						require_once "database.php";

						$table_dirtyWords = $dbo->prepare("SELECT * FROM words");

						if ($table_dirtyWords -> execute()){

							$dataTable_dirtyWords = $table_dirtyWords->fetchAll();
						}

						$data_dirtyWords = array();

						foreach ($dataTable_dirtyWords as $key => $value){

							array_push($data_dirtyWords, $value['word']);
						}

						$f_separator = "/\b";
						$m_separator = "+(|[^a-z])*";
						$e_separator = "+\b/i";
						$dirtyWords = array();

						foreach ($data_dirtyWords as $key => $value){

							$word = "";
							$word = $word . $f_separator;

							for ($i = 0; $i != strlen($value); $i++){

								$word = $word . $value[$i];
								if ($i+1 != strlen($value)){

									$word = $word . $m_separator;
								}
							}

							$word = $word . $e_separator;

							array_push($dirtyWords, $word);
						}

						foreach ($dirtyWords as $dirtyWord) {

							if (preg_match($dirtyWord, $event['message']['text'])) {

								$response = $this->bot->replyText($event['replyToken'], "Astaghfirullahaladzim, jangan berkata kotor :(");
							}
						}
						return $response->getHTTPStatus() . ' ' . $response->getRawBody();
					}

					break;

				case 'join':

					$response = $this->bot->replyText($event['replyToken'], "Thanks for inviting me, i will alert your dirty friend");
					return $response->getHTTPStatus() . ' ' . $response->getRawBody();

					break;

				default:

					echo "undefined Event Type";

					break;
			}
		}
	}
}
?>
