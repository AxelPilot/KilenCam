<?php

// ************************************************************************
/**
 *
 */
class KilenCam
{
	// The relative path to the directory under which all images are uploaded
	// by the webcam.
	protected $imageRelativePath;
	
	// The consistent filename of the image file created by this class
	// to continuously contain the most recent webcam footage.
	protected $imageFileName;
	
	// ************************************************************************
	/**
	 * Constructor
	 * For details, see the comments inside the constructor body.
	 *
	 * @param string $imageRelativePath - The relative path to the directory
	 * 	under which all images are uploaded by the webcam, (relative
	 * 	to the directory where this PHP-file is located).
	 *
	 * @param string $imageFileName - The consistent filename of
	 * 	the image file created by this class to continuously contain the 
	 *	most recent webcam footage.
	 */
	public function __construct( $imageRelativePath, $imageFileName )
	{
		$this->imageRelativePath = $imageRelativePath;
		$this->imageFileName = $imageFileName;

		// Finds the image most recently uploaded to the server by the webcam,
		// copies the image to the same directory that this PHP-file is located in,
		// and renames the copied image as specified in $this->imageFileName.
		$this->copyNewestImage();
		
		// Deletes all images uploaded to the server by the webcam,
		// except for the image that was most recently uploaded.
		$this->deleteUploadedImages();
	} // End of function __construct( $imageRelativePath, $imageFileName )

	// ************************************************************************
	/**
	 * Destructor
	 */
	public function __destruct()
	{
	} // End of function __destruct()

	// ************************************************************************
	/**
	 * Compares two files and returns the path to the file that is most 
	 * recently modified.
	 *
	 * The only exception is if a file contains no data (file size = 0 bytes).
	 * In that case, the file is ignored.
	 *
	 * @param string $file1 - Path to file #1 to be compared.
	 * @param string $file2 - Path to file #2 to be compared.
	 *
	 * Returns false if no files (with file size > 0 bytes) are found.
	 */
	protected function getNewestFile( $file1, $file2 )
	{
		$assoc_array = array();

		if( ( filetype( $file1 ) == "file" ) && ( filesize( $file1 ) > 0 ) )
		{
			$assoc_array[ filectime( $file1 ) ] = $file1;
		}
		
		if( ( filetype( $file2 ) == "file" ) && ( filesize( $file2 ) > 0 ) )
		{
			$assoc_array[ filectime( $file2 ) ] = $file2;
		}

		if( count( $assoc_array ) > 0 )
		{
			krsort( $assoc_array );
			$indexed_array = array_values( $assoc_array );
			reset( $assoc_array );
			return $indexed_array[ 0 ];
		}
		else
		{
			reset( $assoc_array );
			return false;
		}
	} // End of function getNewestFile( $file1, $file2 )
	
	// ************************************************************************
	/**
	 * Returns the relative path to the jpg image file most recently upoaded
	 * to the server by the webcam. The search for the most recently uploaded
	 * file is performed recursively.
	 *
	 * All uploaded images are located in subdirectories below the directory
	 * specified in $this->imageRelativePath.
	 *
	 * @param string $dir - The root directory to perform the file search from.
	 *
	 * Returns false if no files (with file size > 0 bytes) are found.
	 */
	protected function getNewestFileinDir( $dir )
	{
		$filepath = false;
		$tmp = false;
		
		if( is_dir( $dir ) )
		{
			$objects = scandir( $dir );
			foreach( $objects as $object )
			{
				if( $object != "." && $object != ".." )
				{
					if( filetype( $dir . "/" . $object ) == "dir" )
					{
						$tmp = $this->getNewestFileinDir( $dir . "/" . $object );
						$filepath = $this->getNewestFile( $filepath, $tmp );
						$tmp = false;
					}
					elseif( filetype( $dir . "/" . $object ) == "file" )
					{
						$tmp = $dir . "/" . $object;
						$filepath = $this->getNewestFile( $filepath, $tmp );
						$tmp = false;
					}
				}
			} // End of foreach
			reset( $objects );
		}
		return $filepath;
	} // End of function getNewestFileinDir( $dir )
	
	// ************************************************************************
	/**
	 * Finds the image most recently uploaded to the server by the webcam,
	 * copies the image to the same directory that this PHP-file is located in,
	 * and renames the copied image as specified in $this->imageFileName.
	 *
	 * Returns the name of the copied file on success.
	 * Returns false on failure.
	 */
	protected function copyNewestImage()
	{
		if( $filePath = $this->getNewestFileinDir( $this->imageRelativePath ) )
		{
//			chmod( $filePath, 0755 );
			$filenameWE = $this->imageFileName . '.' . pathinfo( $filePath, PATHINFO_EXTENSION );
			
			if( copy( $filePath, $filenameWE ) )
			{
//				chmod( $filenameWE, 0644 );
				return $this->imageFileName;
			}
			else
			{
				return false; // Copy operation failed
			}
		}
		else
		{
			return false; // No files found
		}
	} // End of function copyNewestImage()

	// ************************************************************************
	/**
	 * Checks whether a directory is empty or not.
	 *
	 * @param string $dir - The directory to be checked.
	 *
	 * Returns true if the directory is empty.
	 * Returns false if the directory is not empty.
	 */
	protected function dirIsEmpty( $dir )
	{
		$isEmpty = true;
		if( is_dir( $dir ) )
		{
			$objects = scandir( $dir );
			$n = 0; $c = count( $objects );
			while( ( $n < $c ) && ( $isEmpty ) )
			{
				if( $objects[ $n ] != "." && $objects[ $n ] != ".." )
				{
					if( ( filetype( $dir . "/" . $objects[ $n ] ) == "dir" ) || ( filetype( $dir . "/" . $objects[ $n ] ) == "file" ) )
					{
						$isEmpty = false;
					}
				}
				$n++;
			} // End of while
		}
		return $isEmpty;
	} // End of function dirIsEmpty( $dir )

	// ************************************************************************
	/**
	 * Deletes a directory that is not empty. All content in the directory
         * will be deleted except for the most recently modified file
         * (with file size > 0 bytes)
	 *
	 * @param string $dir - The directory to be deleted.
	 */
	protected function rrmdir( $dir )
	{
		if ( is_dir( $dir ) )
		{
			$objects = scandir( $dir );
			foreach ( $objects as $object )
			{
				if( $object != "." && $object != ".." )
				{
					if( filetype( $dir . "/" . $object ) == "dir" )
					{
						$this->rrmdir( $dir . "/" . $object );
					}
					else
					{
						if( strcmp( trim( $this->getNewestFileinDir( $this->imageRelativePath ) ), trim( $dir . "/" . $object ) ) !== 0 )
						{
//							chmod( $dir . "/" . $object, 0644 );
							unlink( $dir . "/" . $object );
						}
					}
				}
			} // End of foreach
			reset( $objects );

			// Deletes the directory if it is empty
			if( $this->dirIsEmpty( $dir ) )
			{
//				chmod( $dir, 0755 );
				rmdir( $dir );
			}
		}
	} // End of function rrmdir( $dir )

	// ************************************************************************
	/**
	 * Deletes all images uploaded to the server by the webcam,
	 * except for the image most recently uploaded.
	 *
	 * In other words, deletes all files and subdirectories located 
	 * in the directory specified in $this->imageRelativePath, except for
	 * the file that was most recently modified.
	 */
	protected function deleteUploadedImages()
	{
		if ( is_dir( $this->imageRelativePath ) )
		{
			$objects = scandir( $this->imageRelativePath );
			foreach ( $objects as $object )
			{
				if( $object != "." && $object != ".." )
				{
					if( filetype( $this->imageRelativePath . "/" . $object ) == "dir" )
					{
						$this->rrmdir( $this->imageRelativePath . "/" . $object );
					}
					else
					{
						if( strcmp( trim( $this->getNewestFileinDir( $this->imageRelativePath ) ), trim( $this->imageRelativePath . "/" . $object ) ) !== 0 )
						{
							unlink( $this->imageRelativePath . "/" . $object );
						}
					}
				}
			} // End of foreach
			reset( $objects );
		}
	} // End of function deleteUploadedImages()
	
	// ************************************************************************

} // End of class KilenCam
  
// ************************************************************************

?>
