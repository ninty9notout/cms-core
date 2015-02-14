<?php
class VideoItem extends DataObject {
	/**
	 * @var array
	 */
	private static $db = array(
		'Title' => 'Varchar(255)',
		'Description' => 'Text',
		'Source' => 'Varchar(16)',
		'URL' => 'Varchar(2083)',
		'SourceID' => 'Varchar(16)'
	);

	/**
	 * @var array
	 */
	private static $has_one = array(
		'SourceThumbnail' => 'Image',
		'CMSThumbnail' => 'Image'
	);

	/**
	 * @var array
	 */
	private static $casting = array(
		'Description' => 'HTMLText'
	);

	/**
	 * @var array
	 */
	private static $summary_fields = array(
		'Title' => 'Title',
		'Description.Summary' => 'Description',
		'Thumbnail' => 'Thumbnail'
	);

	/**
	 * @var array
	 */
	private static $searchable_fields = array(
		'Title',
		'Description'
	);

	/**
	 * @var array
	 */
	static $allowed_extensions = array(
		'jpg',
		'jpeg',
		'png'
	);

	/**
	 * @var int
	 */
	static $allowed_max_file_size = 10485760; // 10MB

	/**
	 * @var array
	 */
	static $allowed_sources = array(
		'YouTube' => '//www.youtube.com/embed/%s?rel=0&wmode=transparent',
		'Vimeo' => '//player.vimeo.com/video/%s'
	);

	public function getCMSFields() {
		$fields = new FieldList(new TabSet('Root', new Tab('Main')));

		if($this->isNew()) {
			$fields->addFieldToTab('Root.Main', new VideoItemURLField('URL'));
		} else {
			$fields->addFieldsToTab('Root.Main', array(
				new TextField('Title'),
				new TextareaField('Description'),
				new ReadonlyField('URL'),
				$uploadField = new UploadField('CMSThumbnail', 'Thumbnail')
			));

			$uploadField->getValidator()->setAllowedExtensions(static::$allowed_extensions);
			$uploadField->getValidator()->setAllowedMaxFileSize(static::$allowed_max_file_size);

			if(!$this->CMSThumbnailID) {
				$thumbnail = new UploadField('SourceThumbnail');
				$thumbnail->setReadonly(true);
			}
		}

		$this->extend('updateCMSFields', $fields);

		return $fields;
	}

	/**
	 * Return the URL to be used with HTML iframe tags
	 *
	 * @return string
	 */
	public function EmbedURL() {
		if(static::allowed_sources($this->Source)) {
			return sprintf(static::allowed_sources($this->Source), $this->SourceID);
		}

		return $this->URL;
	}

	/**
	 * HTMLText
	 */
	public function Content() {
		if($this->Description) {
			return $this->Description;
		}

		return $this->Title;
	}

	/**
	 * @return Image
	 */
	public function Thumbnail() {
		if($this->CMSThumbnailID) {
			return $this->CMSThumbnail()->CMSThumbnail();
		}

		return $this->SourceThumbnail()->CMSThumbnail();
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @return Image
	 */
	public function CroppedImage($width, $height) {
		if($this->CMSThumbnailID) {
			return $this->CMSThumbnail()->CroppedImage($width, $height);
		}

		return $this->SourceThumbnail()->CroppedImage($width, $height);
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @return Image
	 */
	public function SetRatioSize($width, $height) {
		if($this->CMSThumbnailID) {
			return $this->CMSThumbnail()->SetRatioSize($width, $height);
		}

		return $this->SourceThumbnail()->SetRatioSize($width, $height);
	}

	protected function onBeforeWrite() {
		parent::onBeforeWrite();

		if(!$this->isChanged('URL')) {
			return false;
		}

		$videoDetails = static::video_details($this->URL);

		$this->Title = $videoDetails['title'];
		$this->Description = $videoDetails['description'];
		$this->Source = $videoDetails['source'];
		$this->SourceID = $videoDetails['id'];

		$this->SourceThumbnailID = static::get_file_by_url($videoDetails['thumbnail'], $videoDetails['id'] . '.source')->ID;
		$this->CMSThumbnailID = static::get_file_by_url($videoDetails['thumbnail'], $videoDetails['id'])->ID;
	}

	/**
	 * @return array|string|null
	 */
	public static function allowed_sources($source = null) {
		$sources = Config::inst()->get(get_called_class(), 'allowed_sources', Config::UNINHERITED);

		if($source != null) {
			return !array_key_exists($source, $sources) ?: $sources[$source];
		}

		return $sources;
	}

	/**
	 * Fetch video details from one of the APIs
	 *
	 * @param string $url
	 * @return array
	 */
	public static function video_details($url) {
		$return = array();

		foreach(static::allowed_sources() as $source=>$embed) {
			$func = strtolower($source) . '_id_from_url';

			if($id = static::{$func}($url)) {
				$func = strtolower($source) . '_video_details';

				return array_merge(
					array(
						'source' => $source,
						'id' => $id
					),
					static::{$func}($id)
				);
			}
		}

		return false;
	}

	/**
	 * Use the YouTube v3 API to fetch video details
	 *
	 * @param string $id
	 * @return array
	 */
	private static function youtube_video_details($id) {
		$url = 'https://www.googleapis.com/youtube/v3/videos?';
		$url.= sprintf('key=%s', Config::inst()->get('VideoItem', 'youtube_api_key'));
		$url.= sprintf('&id=%s', $id);
		$url.= '&fields=items(id,snippet(title,description,thumbnails))';
		$url.= '&part=snippet';

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL  => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_BINARYTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_TIMEOUT => 5
		));
		$response = json_decode(curl_exec($ch))->items[0]->snippet;
		curl_close($ch);

		$return = array(
			'title' => $response->title,
			'description' => str_ireplace(array('<br />','<br>','<br/>'), '\n', $response->description),
			'thumbnail' => $response->thumbnails->default->url
		);

		if($response->thumbnails->medium) {
			$return['thumbnail'] = $response->thumbnails->medium->url;
		}

		if($response->thumbnails->high) {
			$return['thumbnail'] = $response->thumbnails->high->url;
		}

		if($response->thumbnails->standard) {
			$return['thumbnail'] = $response->thumbnails->standard->url;
		}

		if($response->thumbnails->maxres) {
			$return['thumbnail'] = $response->thumbnails->maxres->url;
		}

		return $return;
	}

	/**
	 * Extract video ID from the given YouTube URL
	 * See {@link http://blog.luutaa.com/php/extract-youtube-and-vimeo-video-id-from-link/}
	 *
	 * @param string $url
	 * @return string
	 */
	private static function youtube_id_from_url($url) {
		$regex = '~(?:(?:<iframe [^>]*src=")?|(?:(?:<object .*>)?(?:<param .*</param>)*(?:<embed [^>]*src=")?)?)?(?:https?:\/\/(?:[\w]+\.)*(?:youtu\.be/| youtube\.com| youtube-nocookie\.com)(?:\S*[^\w\-\s])?([\w\-]{11})[^\s]*)"?(?:[^>]*>)?(?:</iframe>|</embed></object>)?~ix';

		if(preg_match($regex, $url, $matches) && !empty($matches)) {
			return $matches[1];
		}

		return false;
	}

	/**
	 * Use the Vimeo v2 API to fetch video details
	 *
	 * @param string $id
	 * @return array
	 */
	private static function vimeo_video_details($id) {
		$url = sprintf('http://vimeo.com/api/v2/video/%s.json', $id);

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_TIMEOUT => 30
		));
		$response = json_decode(curl_exec($ch))[0];
		curl_close($ch);

		$return = array(
			'title' => $response->title,
			'description' => str_ireplace(array('<br />','<br>','<br/>'), '\n', $response->description),
			'thumbnail' => $response->thumbnail_small
		);

		if($response->thumbnail_medium) {
			$return['thumbnail'] = $response->thumbnail_medium;
		}

		if($response->thumbnail_large) {
			$return['thumbnail'] = $response->thumbnail_large;
		}

		return $return;
	}

	/**
	 * Extract video ID from the given Vimeo URL
	 * See {@link http://blog.luutaa.com/php/extract-youtube-and-vimeo-video-id-from-link/}
	 *
	 * @param string $url
	 * @return string
	 */
	private static function vimeo_id_from_url($url) {
		$regex = '~(?:<iframe [^>]*src=")?(?:https?:\/\/(?:[\w]+\.)*vimeo\.com(?:[\/\w]*\/videos?)?\/([0-9]+)[^\s]*)"?(?:[^>]*></iframe>)?(?:<p>.*</p>)?~ix';

		if(preg_match($regex, $url, $matches) && !empty($matches)) {
			return $matches[1];
		}

		return false;
	}

	/**
	 * Fetch and save the given thumbnail file
	 *
	 * @param string $url
	 * @param string $filename
	 * @return Image
	 */
	private static function get_file_by_url($url, $filename) {
		$filename = $filename . '.' . pathinfo($url, PATHINFO_EXTENSION);

		$url = str_replace('https://', 'http://', $url);

		$basePath = Director::baseFolder() . DIRECTORY_SEPARATOR;
		$folder = Folder::find_or_make('Videos');
		$relativeFilePath = $folder->Filename . $filename;
		$fullFilePath = $basePath . $relativeFilePath;

		if(!file_exists($fullFilePath)) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			$rawdata = curl_exec($ch);
			curl_close ($ch);

			$fp = fopen($fullFilePath, 'x');
			fwrite($fp, $rawdata);
			fclose($fp);
		}

		$file = new Image();
		$file->ParentID = $folder->ID;
		$file->OwnerID = (Member::currentUser()) ? Member::currentUser()->ID : 0;
		$file->Name = basename($relativeFilePath); 
		$file->Filename = $relativeFilePath; 
		$file->Title = str_replace('-', ' ', substr($filename, 0, (strlen ($filename)) - (strlen (strrchr($filename,'.')))));
		$file->write();

		return $file;
	}
}