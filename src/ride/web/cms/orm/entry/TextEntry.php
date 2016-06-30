<?php

namespace ride\web\cms\orm\entry;

use ride\application\orm\asset\entry\AssetEntry;
use ride\application\orm\entry\TextEntry as OrmTextEntry;

use ride\web\cms\text\Text;

/**
 * Data container for a text
 */
class TextEntry extends OrmTextEntry implements Text {

	/**
	 * Constructs a new instance
	 * @param string $format Name of the format
	 * @param string $body Body text
	 * @return null
	 */
	public function __construct($format = null, $body = null) {
	    $this->format = $format;
	    $this->body = $body;

	    $this->title = null;
	    $this->image = null;
	    $this->imageAlignment = null;
	}

	/**
     * @param AssetEntry $image
     * @return null
     */
    public function setImage(AssetEntry $image = null) {
        if ($this->image === $image) {
            return;
        }
		if ($image instanceof AssetEntry) {
			$this->image = $image;
			return;
		}

        $this->image = null;

        if ($this->entryState === self::STATE_CLEAN) {
            $this->entryState = self::STATE_DIRTY;
        }
    }

}
