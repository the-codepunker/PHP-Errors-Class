<?php
/*
  1. Used to output a debug_backtrace when an error occurs
  2. It registers a shutdown function that is meant to output fatal errors nicely
  3. Parse errors are NOT handled. Actually they can't be handled :)
*/
class Error
{
  private $startTime = 0;

  function __construct()
  {
    $this->startTime = microtime(true);
    ob_start();
      ini_set("display_errors", "on");
      error_reporting(E_ALL);
      set_error_handler(array($this, 'scriptError'));
      register_shutdown_function(array($this, 'shutdown'));
  }

  function scriptError($errno, $errstr, $errfile, $errline)
  {
    if(!headers_sent())
      header("HTTP/1.1 500 Internal Server Error");
    if(ob_get_contents() !== false)
      ob_end_clean();

    switch($errno)
    {
      case E_ERROR:               $errseverity = "Error";             break;
      case E_WARNING:             $errseverity = "Warning";           break;
      case E_NOTICE:              $errseverity = "Notice";            break;
      case E_CORE_ERROR:          $errseverity = "Core Error";        break;
      case E_CORE_WARNING:        $errseverity = "Core Warning";      break;
      case E_COMPILE_ERROR:       $errseverity = "Compile Error";     break;
      case E_COMPILE_WARNING:     $errseverity = "Compile Warning";   break;
      case E_USER_ERROR:          $errseverity = "User Error";        break;
      case E_USER_WARNING:        $errseverity = "User Warning";      break;
      case E_USER_NOTICE:         $errseverity = "User Notice";       break;
      case E_STRICT:              $errseverity = "Strict Standards";  break;
      case E_RECOVERABLE_ERROR:   $errseverity = "Recoverable Error"; break;
      case E_DEPRECATED:          $errseverity = "Deprecated";        break;
      case E_USER_DEPRECATED:     $errseverity = "User Deprecated";   break;
      default:                    $errseverity = "Error";             break;
    }

    $v = debug_backtrace();
    date_default_timezone_set("America/New_York");
    $Date = date("Y M d H:i:s");
    $out = '
    <pre style="border-bottom:1px solid #eee;">
      '.$Date.'
      <span style="color:red;">'.$errseverity.':</font> '.$errstr.'
        <span style="color:#3D9700;">Line '.$errline.': '.$errfile.'</span>
      </span>
      <strong>BACKTRACE:</strong>' . PHP_EOL;
    for ($i = 1; $i<count($v); $i++)
    {
      $out .= "\tLine ".(isset($v[$i]["line"]) ? $v[$i]["line"] : "unknown").": ".(isset($v[$i]["file"]) ? $v[$i]["file"] : "unknown"). PHP_EOL;
      $out .= "\t\tMore: " . PHP_EOL;
      $out .= empty($v[$i]["function"]) ? "" : "\t\t\t" . "Function:"  .   $v[$i]["function"];
      $out .= empty($v[$i]["class"])    ? "" : "\t\t\t" . "Class:"     .   $v[$i]["class"];
      $out .= empty($v[$i]["object"])    ? "" : "\t\t\t" . "Object:"    .   json_encode($v[$i]["object"]);
    }
    $out.='</span></pre>';
    echo $out;
  }

  function shutdown()
  {
    $isError = false;
    if ($error = error_get_last())
    {
      switch($error['type'])
      {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
        case E_RECOVERABLE_ERROR:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
          $isError = true;
          $this->scriptError($error['type'], $error['message'], $error['file'], $error['line']);
          break;
      }
    }
  }
}
