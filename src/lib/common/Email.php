<?php
/*
 * Synopsy PHP Framework (c) by Webdesign Studio s.r.o.
 * 
 * Synopsy PHP Framework is licensed under a
 * Creative Commons Attribution 4.0 International License.
 *
 * You should have received a copy of the license along with this
 * work. If not, see <http://creativecommons.org/licenses/by/4.0/>.
 *
 * Any files in this application that are NOT marked with this disclaimer are
 * not part of the framework's open-source implementation, the CC 4.0 licence
 * does not apply to them and are protected by standard copyright laws!
 */

namespace Synopsy\Common;

use PHPMailer;
use Synopsy\Config\Config;
use Synopsy\Exceptions\SynopsyException;
use Synopsy\Form\Validate;

/**
 * Class used to create and send HTML emails.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Email {
    
    /**
     * PHPMailer instance
     * 
     * @var PHPMailer
     */
    private $phpMailer = null;
    
    /**
     * Constructor. Includes PHPMailer library and initializes basic mailer
     * settings.
     * 
     */
    public function __Construct() {
	require_once(DIR.'resources/plugins/phpmailer/PHPMailerAutoload.php');
	$websiteConfig = Config::get('website');
        $cf = Config::get('email');
        $configFile = APP.'config/email/'.$cf.'.ini';
        if (!file_exists($configFile)) {
            throw new SynopsyException("Email configuration file '$configFile' doesn't exist!");
        }
        $emailConfig = parse_ini_file($configFile);
	$this->phpMailer = new PHPMailer();
	$this->phpMailer->isSMTP();
	$this->phpMailer->CharSet = 'utf-8';
	$this->phpMailer->Host = $emailConfig['host'];
	$this->phpMailer->SMTPAuth = true;
	$this->phpMailer->SMTPDebug = ($emailConfig['debug'] == 'true');
	$this->phpMailer->Username = $emailConfig['user'];
	$this->phpMailer->Password = $emailConfig['password'];
	$this->phpMailer->SMTPSecure = 'tls';
	$this->phpMailer->From = $emailConfig['user'];
	$this->phpMailer->FromName = (string) $websiteConfig->name;
	$this->phpMailer->isHTML(true);
    }
    
    /**
     * Adds recipient to the email. Address must be a valid email address.
     * 
     * @param String $address
     * @throws SynopsyException
     */
    public function addAddress($address) {
	if ($address == null) {
	    throw new SynopsyException('Parameter $address can\'t be null!');
	} elseif (!Validate::regex(Validate::EMAIL_REGEX,$address)) {
	    throw new SynopsyException("Value $address for parameter \$address is not a valid email address!");
	}
	$this->phpMailer->addAddress($address);
    }
    
    /**
     * Adds carbon copy to the email. Address must be a valid email address.
     * 
     * @param String $cc
     * @throws SynopsyException
     */
    public function addCc($cc) {
	if ($cc == null) {
	    throw new SynopsyException('Parameter $cc can\'t be null!');
	} elseif (!Validate::regex(Validate::EMAIL_REGEX,$cc)) {
	    throw new SynopsyException("Value $cc for parameter \$cc is not a valid email address!");
	}
	$this->phpMailer->addCC($cc);
    }
    
    /**
     * Adds blind carbon copy to the email. Address must be a valid email address.
     * 
     * @param String $bcc
     * @throws SynopsyException
     */
    public function addBcc($bcc) {
	if ($bcc == null) {
	    throw new SynopsyException('Parameter $bcc can\'t be null!');
	} elseif (!Validate::regex(Validate::EMAIL_REGEX,$bcc)) {
	    throw new SynopsyException("Value $bcc for parameter \$bcc is not a valid email address!");
	}
	$this->phpMailer->addBCC($bcc);
    }
    
    /**
     * Adds subject to the message.
     * 
     * @param String $subject
     * @throws SynopsyException
     */
    public function setSubject($subject) {
	if ($subject == null) {
	    throw new SynopsyException('Parameter $subject can\'t be null!');
	}
	$this->phpMailer->Subject = $subject;
    }
    
    /**
     * Adds HTML body to the message.
     * 
     * @param String $body
     * @throws SynopsyException
     */
    public function setBody($body) {
	if ($body == null) {
	    throw new SynopsyException('Parameter $body can\'t be null!');
	}
	$this->phpMailer->Body = $body;
    }
    
    /**
     * Submits email.
     * 
     * @return Boolean
     */
    public function send() {
	return $this->phpMailer->send();
    }
    
}
