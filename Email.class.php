<?php

/**
  * Email.class.php
  * Yet another php mail sender, can send plain text, html, and include attachments.
  * Version: 0.0.1 Alpha
  * Created By: Andrew Burton
**/

class Emailer
{
    
	/**
	  * @private
	  * @var string
	**/
	private $_ReplyToEmail;
	
	/**
	  * @private
	  * @var string
	**/
	private $_SendFromEmail;
	
	/**
	  * @private
	  * @var string
	**/
	private $_SendToEmail;
	
	/**
	  * @private
	  * @var string
	**/
	private $_SendToName;
	
	/**
	  * @private
	  * @var string
	**/
	private $_SendSubject;
	
	/**
	  * @private
	  * @var string
	**/
	private $_SendBody;
	
	/**
	  * @private
	  * @var "plain" or "html"
	**/
	private $_SendType;
	
	/**
	  * @private
	  * @var "phpmail", "smtp", "gmail"
	**/
	private $_SendUsing;
	
	/**
	  * @private
	  * @var string
	**/
	private $_SendAttachment;
	
	/**
	  * @private
	  * @var array
	**/
	private $_SendErrors = array();
	
	/**
	  * @private
	  * @var bool
	**/
	private $_HasErrors;
	
	/**
	  * @private
	  * @var bool
	**/
	private $_HasAttachemnts;
	
	private $_SendHeaders;
	private $_RandHash;
	
	/**
	  * @private
	  * Sets initial information.
	**/
	private function __construct()
	{
		$this->_SendType = "plain";
		$this->_HasErrors = false;
		$this->_HasAttachemnts = false;
		$this->_SendUsing = "phpmail";
		$this->_RandHash = md5(date('r', time()));
		$this->_SendUsing = "phpmail";
	}
	
	/**
	  * @public
	  * @string email
	  * @string name
	  * Sets To information.
	**/
	public function SetTo($email, $name = 'NONE')
	{
		if($name == 'NONE')
			$this->_SendToName = $name;
		$this->_SendToEmail = $email;
	}
	
	/**
	  * @public
	  * @string email
	  * @string name
	  * Sets from information.
	**/
	public function SetFrom($email, $reply = 'NONE')
	{
		if($reply == 'NONE')
			$this->_ReplyToEmail = $email;
		else
			$this->_ReplyToEmail = $reply;
		$this->_SendFromEmail = $email;
	}
	
	/**
	  * @public
	  * @string subj
	  * Sets the subject of the email.
	**/
	public function SetSubject($subj)
	{
		$this->_SendSubject = $subj;
	}
	
	/**
	  * @public
	  * @string body
	  * @string type "plain" or "html"
	  * Sets the body of the email.
	**/
	public function SetBody($body, $type = 'plain'){
		if($type == 'plain')
			$this->_SendType = "plain";
		else
			$this->_SendType = "html";
		$this->_SendBody = $body;
	}
	
	/**
	  * Send a email message.
	**/
	public function SendMessage()
	{
		if(strlen($this->_SendFromEmail) > 0 && strlen($this->_ReplyToEmail) > 0 && strlen($this->_SendToEmail) > 0 && strlen($this->_SendToName) > 0 && strlen($this->_SendSubject) > 0 && strlen($this->_SendBody) > 0)
		{
			if($this->_SendType == "plain" && $this->_HasAttachemnts == false)
			{
				if($this->_SendUsing == "phpmail")
					return $this->SendPlain(false);
			}
			elseif($this->_SendType == "html" && $this->_HasAttachemnts == false)
			{
				if($this->_SendUsing == "phpmail")
					return $this->SendHTML(false);
			}
		}
		else
		{
			$this->_HasErrors = true;
			return false;
		}
	}
	
	/**
	  * Send a plain email.
	**/
	private function SendPlain($attach)
	{
		if($attach == true)
		{
			$headers = "From: ".$this->_SendFromEmail."\r\nReply-To: ".$this->_ReplyToEmail;
			$headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$this->_RandHash."\"";
		}
		else
		{
			$headers = "From: ".$this->_SendFromEmail."\r\nReply-To: ".$this->_ReplyToEmail;
		}
		ob_start();
		?>
--PHP-alt-<?php echo $this->_RandHash; ?>  
Content-Type: text/html; charset="iso-8859-1" 
Content-Transfer-Encoding: 7bit

<?php echo $this->_SendBody; ?>

--PHP-alt-<?php echo $this->_RandHash; ?>--
		<?php
		$message = ob_get_clean();
		if(mail($this->_SendToEmail,$this->_SendSubject,$message,$headers))
			return true;
		else
			return false;
	}
	
	/**
	  * Send a HTML email message.
	**/
	private function SendHTML($attach)
	{
		$headers = "From: ".$this->_SendFromEmail."\r\nReply-To: ".$this->_ReplyToEmail;
		if($attach)
		{
			$headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$this->_RandHash."\"";
			$attachment = chunk_split(base64_encode(file_get_contents($this->_SendAttachment)));
			ob_start();
		?>
--PHP-alt-<?php echo $this->_RandHash; ?>  
Content-Type: text/html; charset="iso-8859-1" 
Content-Transfer-Encoding: 7bit

<?php echo $this->_SendBody; ?>

--PHP-alt-<?php echo $this->_RandHash; ?>--

--PHP-mixed-<?php echo $this->_RandHash; ?>  
Content-Type: <?php echo mime_content_type($this->_SendAttachment); ?>; name="<?php echo $this->_SendAttachment; ?>"  
Content-Transfer-Encoding: base64  
Content-Disposition: attachment  
<?php echo $attachment; ?> 
--PHP-mixed-<?php echo $random_hash; ?>-- 
		<?php
			$message = ob_get_clean();
		}
		else
		{
			ob_start();
		?>
--PHP-alt-<?php echo $this->_RandHash; ?>  
Content-Type: text/html; charset="iso-8859-1" 
Content-Transfer-Encoding: 7bit

<?php echo $this->_SendBody; ?>

--PHP-alt-<?php echo $this->_RandHash; ?>--
		<?php
			$message = ob_get_clean();
		}
		if(mail($this->_SendToEmail,$this->_SendSubject,$this->_SendBody,$headers))
			return true;
		else
			return false;
	}
	
}

if(!function_exists('mime_content_type'))
{

    function mime_content_type($filename)
	{

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types))
		{
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open'))
		{
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else
		{
            return 'application/octet-stream';
        }
    }
}

?>