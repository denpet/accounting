<?php

namespace App\Services;

use ErrorException;
use Exception;

class ZKTecoService
{
    const USHRT_MAX = 65535;


    const CMD_CONNECT = 1000; // Conecction request
    const CMD_EXIT = 1001; // Disconnection request
    const CMD_ENABLEDEVICE = 1002;  // Ensure the machine to be at normal work condition
    const CMD_DISABLEDEVICE = 1003; // Make the machine to be at the shut-down condition, generally demontration in the work on LCD
    const CMD_RESTART = 1004; // Restart the machine
    const CMD_POWEROFF = 1005; // Shut-down power source
    const CMD_SLEEP = 1006; // Ensure the machine to be at the idle state
    const CMD_RESUME = 1007; // Awakens the sleep machine (temporarily not to support)

    const CMD_CAPTUREFINGER = 1009; // Captures fingerprints picture
    const CMD_TEST_TEMP = 1011; // Test some fingerprint exist or does not
    const CMD_CAPTUREIMAGE = 1012; // Capture the entire image

    const CMD_REFRESHDATA = 1013; // Refresh the machine interior data
    const CMD_REFRESHOPTION = 1014; // Refresh the configuration parameter

    const CMD_TESTVOICE = 1017; // Play voice

    const CMD_VERSION = 1100; // Obtain the firmware edition
    const CMD_CHANGE_SPEED = 1101; // Change transmission speed
    const CMD_AUTH = 1102; // Connection authorizations

    const CMD_PREPARE_DATA = 1500; // Prepare to transmit the data
    const CMD_DATA = 1501; // Transmit a data packet
    const CMD_FREE_DATA = 1502; // Clear machine opened buffer

    const CMD_DB_RRQ = 7; // Read in some kind of data from the machine
    const CMD_DB_WRQ = 8; // Upload th user information

    const CMD_USERTEMP_RRQ = 9; // Read some fingerprint template or some kind of data entirely
    const CMD_USERTEMP_WRQ = 10; // Upload some fingerprint template
    const CMD_OPTIONS_RRQ = 11; // Read in the machine some configuration parameter
    const CMD_OPTIONS_WRQ = 12; // Set machine configuration parameter
    const CMD_ATTLOG_RRQ = 13; // Read all attendance record
    const CMD_CLEAR_DATA = 14; // Celar data
    const CMD_CLEAR_ATTLOG = 15; // Clear attendance records
    const CMD_DELETE_USER = 18; // Delete some user
    const CMD_DELETE_USERTEMP = 19; // Delete some fingerprint template
    const CMD_CLEAR_ADMIN = 20; // Cancel the manager

    const CMD_USERGRP_RRQ = 21; // Read the user grouping
    const CMD_USERGRP_WRQ = 22; // Set users grouping

    const CMD_USERTZ_RRQ = 23; // Read the user Time Zone set
    const CMD_USERTZ_WRQ = 24; // Write the user Time Zone set

    const CMD_GRPTZ_RRQ = 25; // Read the group Time Zone Set
    const CMD_GRPTZ_WRQ = 26; // Write the group Time Zone Set

    const CMD_TZ_RRQ = 27; // Read Time Zone Set
    const CMD_TZ_WRQ = 27; // Write Time Zone

    const CMD_ULG_RRQ = 29; // Read unlocks combination
    const CMD_ULG_WRQ = 30; // Write unlocks combination

    const CMD_UNLOCK = 31; // Unlock
    const CMD_CLEAR_ACC = 32; // Restore Access Control set to the default condition

    const CMD_CLEAR_OPLOG = 33; // Delete attendance machine all attendance record
    const CMD_OPLOG_RRQ = 34; // Read manages the record
    const CMD_GET_FREE_SIZES = 50; // Obtain machines condition, like user recording number and so on

    const CMD_ENABLE_CLOCK = 57; // Ensure the machine to be at the normal work condition
    const CMD_STARTVERIFY = 60; // Ensure the machine to be at the authentication condition
    const CMD_STARTENROLL = 61; // Start to enroll some user, ensure the machines to be at the registration user condition

    const CMD_CANCELCAPTURE = 62; // Make the machine to be at the waiting order status, please refers to the CMD_STARTENROLL description
    const CMD_STATE_RRQ = 64; // Gain the machine the condition
    const CMD_WRITE_LCD = 66; // Write LCD
    const CMD_CLEAR_LCD = 67; // Clear the LCD captions (clear sreen)
    const CMD_GET_PINWIDTH = 69; // Obtain the length of user's serial number

    const CMD_SMS_WRQ = 70; // Upload the short message
    const CMD_SMS_RRQ = 71; // Download the short message
    const CMD_DELETE_SMS = 72; // Delete the short message

    const CMD_UDATA_WRQ = 73; // Set user's short message
    const CMD_DELETE_UDATA = 74; // Delete user's short message

    const CMD_DOORSTATE_RRQ = 75; // Obtain the door condition

    const CMD_WRITE_MIFARE = 76; // Write the MiFare card
    const CMD_EMPTY_MIFARE = 78; // Clear the MiFare card

    const CMD_GET_TIME = 201; // Obtain the machine time
    const CMD_SET_TIME = 202; // Set the machine time

    const CMD_REG_EVENT = 500; // Register the Event


    const EF_ATTLOG = 1; // Be real-time to verify successfully
    const EF_FINGER = 1 << 1; // Be real-time to press fingerprint (be real-time to return data type sign)
    const EF_ENROLLUSER = 1 << 2; // Be real-time to enroll user
    const EF_ENROLLFINGER = 1 << 3; // Be real-time to enroll fingerprint
    const EF_BUTTON = 1 << 4; // Be real-time to press button
    const EF_UNLOCK = 1 << 5; // Be real-time to unlock
    const EF_VERIFY = 1 << 7; // Be real-time to verify fingerprint
    const EF_FPFTR = 1 << 8; // Be real-time capture fingerprint
    const EF_ALARM = 1 << 9; // Alarm signal

    const CMD_ACK_OK = 2000; // Return value for order perform successfully
    const CMD_ACK_ERROR = 2001; // Return value for order perform failed
    const CMD_ACK_DATA = 2002; // Return Data
    const CMD_ACK_RETRY = 2003; // Registered event occorred
    const CMD_ACK_REPEAT = 2004;
    const CMD_ACK_UNAUTH = 2005; // Connection unauthorized
    const CMD_ACK_UNKNOWN = 0xffff; // Unknown order
    const CMD_ACK_ERROR_CMD = 0xfffd; // order false
    const CMD_ACK_ERROR_INIT = 0xfffc; // Not Initialized
    const CMD_ACK_ERROR_DATA = 0xfffb;

    const CMD_DEVICE = 11;

    const CMD_SET_USER = 8;

    const LEVEL_USER = 0;
    const LEVEL_ADMIN = 14;

    private $ip;
    private $port;
    private $zkclient;
    private $data_recv = '';
    private $session_id = 0;
    private $userdata = array();
    private $attendancedata = array();

    private function encode_time($t)
    {
        /*Encode a timestamp send at the timeclock

        copied from zkemsdk.c - EncodeTime*/
        $d = (($t->year % 100) * 12 * 31 + (($t->month - 1) * 31) + $t->day - 1) *
            (24 * 60 * 60) + ($t->hour * 60 + $t->minute) * 60 + $t->second;

        return $d;
    }

    private function decode_time($t)
    {
        /*Decode a timestamp retrieved from the timeclock

        copied from zkemsdk.c - DecodeTime*/
        $second = $t % 60;
        $t = $t / 60;

        $minute = $t % 60;
        $t = $t / 60;

        $hour = $t % 24;
        $t = $t / 24;

        $day = $t % 31 + 1;
        $t = $t / 31;

        $month = $t % 12 + 1;
        $t = $t / 12;

        $year = floor($t + 2000);

        $d = date("Y-m-d H:i:s", strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . $second));

        return $d;
    }

    /**
     * This function calculates the chksum of the packet to be sent to the time clock 
     */
    private function createChkSum($p)
    {
        $l = count($p);
        $chksum = 0;
        $i = $l;
        $j = 1;
        while ($i > 1) {
            $u = unpack('S', pack('C2', $p['c' . $j], $p['c' . ($j + 1)]));

            $chksum += $u[1];

            if ($chksum > self::USHRT_MAX)
                $chksum -= self::USHRT_MAX;
            $i -= 2;
            $j += 2;
        }

        if ($i)
            $chksum = $chksum + $p['c' . strval(count($p))];

        while ($chksum > self::USHRT_MAX)
            $chksum -= self::USHRT_MAX;

        if ($chksum > 0)
            $chksum = - ($chksum);
        else
            $chksum = abs($chksum);

        $chksum -= 1;
        while ($chksum < 0)
            $chksum += self::USHRT_MAX;

        return pack('S', $chksum);
    }

    /**
     * This function puts a the parts that make up a packet together and packs them into a byte string
     */
    private function createHeader($command, $chksum, $session_id, $reply_id, $command_string)
    {
        $buf = pack('SSSS', $command, $chksum, $session_id, $reply_id) . $command_string;

        $buf = unpack('C' . (8 + strlen($command_string)) . 'c', $buf);

        $u = unpack('S', $this->createChkSum($buf));
        if (is_array($u)) {
            $u = array_values($u)[0];
        }
        $chksum = $u;

        $reply_id += 1;

        if ($reply_id >= self::USHRT_MAX)
            $reply_id -= self::USHRT_MAX;

        $buf = pack('SSSS', $command, $chksum, $session_id, $reply_id);

        return $buf . $command_string;
    }

    /**
     * Checks a returned packet to see if it returned CMD_ACK_OK, indicating success
     */
    private function checkValid($reply)
    {

        $u = unpack('H2h1/H2h2', substr($reply, 0, 8));

        $command = hexdec($u['h2'] . $u['h1']);
        if ($command == self::CMD_ACK_OK)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Connect to Time Attendance module
     */
    public function connect($ip, $port)
    {
        $this->ip = $ip;
        $this->port = $port;

        $this->zkclient = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        $timeout = array('sec' => 60, 'usec' => 500000);
        socket_set_option($this->zkclient, SOL_SOCKET, SO_RCVTIMEO, $timeout);

        $command = self::CMD_CONNECT;
        $command_string = '';
        $chksum = 0;
        $session_id = 0;
        $reply_id = -1 + self::USHRT_MAX;

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);
        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);
            if (strlen($this->data_recv) > 0) {
                $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

                $this->session_id =  hexdec($u['h6'] . $u['h5']);
                return $this->checkValid($this->data_recv);
            } else
                return FALSE;
        } catch (ErrorException $e) {
            return FALSE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * Disconnect from Time Attendance module
     */
    public function disconnect()
    {
        $command = self::CMD_EXIT;
        $command_string = '';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);


        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);
        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            return $this->checkValid($this->data_recv);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (Exception $e) {
            return FALSE;
        }
    }


    /**
     * Present version of Time Attendance module
     */
    public function version()
    {
        $command = self::CMD_VERSION;
        $command_string = '';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (Exception $e) {
            return False;
        }
    }


    /**
     * Return the OS Version of the Time Attendance module
     */
    public function osVersion()
    {
        $command = self::CMD_DEVICE;
        $command_string = '~OS';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the platform version of the Time Attendance module
     */
    public function platform()
    {
        $command = self::CMD_DEVICE;
        $command_string = '~Platform';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (Exception $e) {
            return False;
        }
    }

    /**
     * Return the firmware version of the Time Attendance module
     */
    public function fmVersion()
    {
        $command = self::CMD_DEVICE;
        $command_string = '~ZKFPVersion';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the work code of the Time Attendance module
     */
    public function workCode()
    {
        $command = self::CMD_DEVICE;
        $command_string = 'WorkCode';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function ssr()
    {
        $command = self::CMD_DEVICE;
        $command_string = '~SSR';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function pinWidth()
    {
        $command = self::CMD_DEVICE;
        $command_string = '~PIN2Width';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function faceFunctionOn()
    {
        $command = self::CMD_DEVICE;
        $command_string = 'FaceFunOn';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function serialNumber()
    {
        $command = self::CMD_DEVICE;
        $command_string = '~SerialNumber';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function deviceName()
    {
        $command = self::CMD_DEVICE;
        $command_string = '~DeviceName';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function disableDevice()
    {
        $command = self::CMD_DISABLEDEVICE;
        $command_string = chr(0) . chr(0);
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function enableDevice()
    {
        $command = self::CMD_ENABLEDEVICE;
        $command_string = '';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    private function getSizeUser()
    {
        /*Checks a returned packet to see if it returned CMD_PREPARE_DATA,
        indicating that data packets are to be sent

        Returns the amount of bytes that are going to be sent*/
        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $command = hexdec($u['h2'] . $u['h1']);

        if ($command == self::CMD_PREPARE_DATA) {
            $u = unpack('H2h1/H2h2/H2h3/H2h4', substr($this->data_recv, 8, 4));
            $size = hexdec($u['h4'] . $u['h3'] . $u['h2'] . $u['h1']);
            return $size;
        } else
            return FALSE;
    }


    /**
     * Return the  of the Time Attendance module
     */
    public function getUser()
    {
        $command = self::CMD_USERTEMP_RRQ;
        $command_string = chr(5);
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            if ($this->getSizeUser()) {
                $bytes = $this->getSizeUser();

                while ($bytes > 0) {
                    @socket_recvfrom($this->zkclient, $data_recv, 1032, 0, $this->ip, $this->port);
                    array_push($this->userdata, $data_recv);
                    $bytes -= 1024;
                }

                $this->session_id =  hexdec($u['h6'] . $u['h5']);
                @socket_recvfrom($this->zkclient, $data_recv, 1024, 0, $this->ip, $this->port);
            }


            $users = array();
            if (count($this->userdata) > 0) {
                //The first 4 bytes don't seem to be related to the user
                for ($x = 0; $x < count($this->userdata); $x++) {
                    if ($x > 0)
                        $this->userdata[$x] = substr($this->userdata[$x], 8);
                }

                $userdata = implode('', $this->userdata);

                $userdata = substr($userdata, 11);

                while (strlen($userdata) > 72) {

                    $u = unpack('H144', substr($userdata, 0, 72));

                    $u1 = hexdec(substr($u[1], 2, 2));
                    $u2 = hexdec(substr($u[1], 4, 2));
                    $uid = $u1 + ($u2 * 256);
                    $cardno = hexdec(substr($u[1], 78, 2) . substr($u[1], 76, 2) . substr($u[1], 74, 2) . substr($u[1], 72, 2)) . ' ';
                    $role = hexdec(substr($u[1], 4, 4)) . ' ';
                    $password = hex2bin(substr($u[1], 8, 16)) . ' ';
                    $name = hex2bin(substr($u[1], 24, 74)) . ' ';
                    $userid = hex2bin(substr($u[1], 98, 72)) . ' ';

                    //Clean up some messy characters from the user name
                    $password = explode(chr(0), $password, 2);
                    $password = $password[0];
                    $userid = explode(chr(0), $userid, 2);
                    $userid = $userid[0];
                    $name = explode(chr(0), $name, 3);
                    $name = utf8_encode($name[0]);
                    $cardno = str_pad($cardno, 11, '0', STR_PAD_LEFT);

                    if ($name == "")
                        $name = $uid;

                    $users[$uid] = array($userid, $name, $cardno, $uid, intval($role), $password);

                    $userdata = substr($userdata, 72);
                }
            }

            return $users;
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function setUser($uid, $userid, $name, $password, $role)
    {
        $command = self::CMD_SET_USER;
        $command_string = pack('axaa8a28aa7xa8a16', chr($uid), chr($role), $password, $name, chr(1), '', $userid, '');
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);
            //            file_put_contents('set_user_binary_log', $this->data_recv.PHP_EOL.'========================='.PHP_EOL, FILE_APPEND);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function getFP()
    {
        $command = chr(227) . chr(17) . chr(18);
        $command_string = chr(80) . chr(16) . chr(3) . chr(253) . chr(132) . chr(64) . chr(0) . chr(0);
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);
        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);
            return '';
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function clearUser()
    {
        $command = self::CMD_CLEAR_DATA;
        $command_string = '';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function clearAdmin()
    {
        $command = self::CMD_CLEAR_ADMIN;
        $command_string = '';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function getAttendance()
    {
        $command = self::CMD_ATTLOG_RRQ;
        $command_string = '';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        //try {
        @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

        if ($this->getSizeAttendance()) {
            $bytes = $this->getSizeAttendance();
            while ($bytes > 0) {
                @socket_recvfrom($this->zkclient, $data_recv, 1032, 0, $this->ip, $this->port);
                array_push($this->attendancedata, $data_recv);
                $bytes -= 1024;
            }

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            @socket_recvfrom($this->zkclient, $data_recv, 1024, 0, $this->ip, $this->port);
        }

        $attendance = array();
        if (count($this->attendancedata) > 0) {
            # The first 4 bytes don't seem to be related to the user
            for ($x = 0; $x < count($this->attendancedata); $x++) {
                if ($x > 0)
                    $this->attendancedata[$x] = substr($this->attendancedata[$x], 8);
            }

            $attendancedata = implode('', $this->attendancedata);
            $attendancedata = substr($attendancedata, 10);

            while (strlen($attendancedata) > 40) {

                $u = unpack('H78', substr($attendancedata, 0, 39));
                $u1 = hexdec(substr($u[1], 4, 2));
                $u2 = hexdec(substr($u[1], 6, 2));
                $uid = $u1 + ($u2 * 256);
                $id = intval(str_replace("\0", '', hex2bin(substr($u[1], 6, 8))));
                $state = hexdec(substr($u[1], 56, 2));
                $timestamp = $this->decode_time(hexdec($this->reverseHex(substr($u[1], 58, 8))));

                array_push($attendance, array($uid, $id, $state, $timestamp));

                $attendancedata = substr($attendancedata, 40);
            }
        }

        return $attendance;
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function clearAttendance()
    {
        $command = self::CMD_CLEAR_ATTLOG;
        $command_string = '';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function setTime($t)
    {
        $command = self::CMD_SET_TIME;
        $command_string = pack('I', $this->encode_time($t));
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function getTime()
    {
        $command = self::CMD_GET_TIME;
        $command_string = '';
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return $this->decode_time(hexdec($this->reverseHex(bin2hex(substr($this->data_recv, 8)))));
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Return the  of the Time Attendance module
     */
    public function enrollUser($userid)
    {
        $command = self::CMD_STARTENROLL;
        $command_string = pack("a*", $userid);
        $chksum = 0;
        $session_id = $this->session_id;

        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);

        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

        socket_sendto($this->zkclient, $buf, strlen($buf), 0, $this->ip, $this->port);

        try {
            @socket_recvfrom($this->zkclient, $this->data_recv, 1024, 0, $this->ip, $this->port);

            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->data_recv, 0, 8));

            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->data_recv, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return False;
        }
    }

    /**
     * Checks a returned packet to see if it returned CMD_PREPARE_DATA,
     * indicating that data packets are to be sent
     * Returns the amount of bytes that are going to be sent
     */
    private function getSizeAttendance()
    {
        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->data_recv, 0, 8));
        $command = hexdec($u['h2'] . $u['h1']);

        if ($command == self::CMD_PREPARE_DATA) {
            $u = unpack('H2h1/H2h2/H2h3/H2h4', substr($this->data_recv, 8, 4));
            $size = hexdec($u['h4'] . $u['h3'] . $u['h2'] . $u['h1']);
            return $size;
        } else
            return FALSE;
    }

    private function reverseHex($hexstr)
    {
        $tmp = '';

        for ($i = strlen($hexstr); $i >= 0; $i--) {
            $tmp .= substr($hexstr, $i, 2);
            $i--;
        }

        return $tmp;
    }
}
