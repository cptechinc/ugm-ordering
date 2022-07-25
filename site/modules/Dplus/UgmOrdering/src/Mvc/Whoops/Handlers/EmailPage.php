<?php namespace UgmOrdering\Mvc\Whoops\Handlers;
// ProcessWire
use ProcessWire\ProcessWire;

use Mvc\Whoops\Handlers\Page;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Page Handler
 * Wrappper Class to hold WhoopsHandler Statically so data
 * could be added to the Handler
 */
class EmailPage extends Page {

	private static $handler;

	/**
	 * Return the Handler
	 * @return WhoopsHandler
	 */
	public static function handler() {
		if (empty(self::$handler)) {
			self::$handler = new self();
		}
		return self::$handler;
	}

	public function writeTmpFile($html) {
		return file_put_contents($this->getTmpFilename(), $html);
	}

	protected function getTmpFilename() {
		return '/tmp/'.session_id().'-error.txt';
	}

	public function handle() {
		parent::handle();
		$output = ob_get_clean();
		$this->writeTmpFile($output);

		// $mail = new PHPMailer(true);
		// // $mail->setFrom('errors@ugm.com', 'UGM ORDERING');
		// $mail->addAddress('paul@cptechinc.com', 'Dev');     //Add a recipient
		// $mail->addAttachment($this->getTmpFilename());
		// $mail->isHTML(true);                                  //Set email format to HTML
		// $mail->Subject = 'Ordering Error';
		// $mail->Body    = $output;
		// echo var_dump($mail->send());

		$pw = ProcessWire::getCurrentInstance();
		$message = $pw->wire('mail')->new();
		$message->subject('Ordering Errors')
		  ->to('paul@cptechinc.com')
		  // ->from('errors@ugm.com')
		  ->body('Ordering Errors')
		  ->attachment($this->getTmpFilename());
		$numSent = $message->send();
		echo $numSent;
	}
}
