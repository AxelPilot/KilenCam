<?php

// ************************************************************************
// ************************************************************************
// *********** MODIFY THIS FILE AS DESCRIBED BELOW ************************
// ************************************************************************
// ************************************************************************

// ************************************************************************
// Modify the variable $error_email to contain the email address where you
// want runtime error messages to be sent.

$error_email = "";

// ************************************************************************
// Modify the variable $image_relative_path to contain the relative path to
// the base directory of the directory structure containing images uploaded
// from webcam.

$image_relative_path = "kilen";

// ************************************************************************
// The variable $image_filename contains the file name (without extension)
// of the image file, which this script updates (or creates if it doesn't
// already exist) to contain the most recently uploaded webcam image every
// time the script runs.
//
// The correct file extension (e.g. jpg) is automatically added by the
// script.
//
// You can modify the variable $image_filname to your liking. (Remember, NO
// file extension!)

$image_filname = "kilen";

// ************************************************************************



// ************************************************************************
// Relative path of the error log file.

$errorlogpath = "./errorlog.kilencam.txt";

// ************************************************************************



// ************************************************************************
// ************************************************************************
// ************************************************************************
//
//  DO NOT EDIT ANY OF THE CODE BELOW!
//
// ************************************************************************
// ************************************************************************
// ************************************************************************


// ************************************************************************
/**
 * Custom function for execution on shutdown.
 */
function shutdown()
{
	global $error_email;
	global $errorlogpath;

	$error = error_get_last();
	if( $error[ 'type' ] === E_ERROR )
	{
		$msg = $error[ 'message' ] . "\r\n";

		error_log( $msg, 1, $error_email );
		error_log( $msg, 3, $errorlogpath );
	}	
}

// ************************************************************************

register_shutdown_function( 'shutdown' );

// ************************************************************************
/**
 * Custom error handler.
 */
function myErrorHandler( $errno, $errstr, $errfile, $errline )
{
	global $error_email;
	global $errorlogpath;

	$mail_error = date( 'd.m.Y H:i' ) . "\r\n";
	$mail_error .= $errfile . "\r\n";
	$mail_error .= $errline . "\r\n";
	$mail_error .= $errno . "\r\n";
	$mail_error .= $errstr . "\r\n";

	$error = date( 'd.m.Y H:i' ) . ' ';
	$error .= $errfile . ' ';
	$error .= $errline . ' ';
	$error .= $errno . ' ';
	$error .= $errstr . "\r\n";

	error_log( $mail_error, 1, $error_email );
	error_log( $error, 3, $errorlogpath );
}

// ************************************************************************

set_error_handler( 'myErrorHandler', E_ALL );

// ************************************************************************
/**
 * Custom class autoloader.
 */
function custom_autoloader( $class )
{
	require_once './classes/' . $class . '.php';
}

// ************************************************************************

spl_autoload_register( 'custom_autoloader' );

// ************************************************************************

?>