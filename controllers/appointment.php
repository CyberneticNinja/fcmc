<?php
/**
 * Created by PhpStorm.
 * User: lamp
 * Date: 6/12/18
 * Time: 9:19 PM
 */

namespace controllers;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;
use Carbon\Carbon;
use PHPMailer\PHPMailer\PHPMailer;


class appointment
{
    /**
     * contact constructor.
     */
    public function __construct(ContainerInterface $container) {
        // constructor receives container instance
        $this->container = $container;
    }

    protected function validation(array $inputs)
    {
        $errors = array();

        foreach ($inputs as $input)
        {
            if(!$this->isNotEmpty($input))
            {
                $errors[] = 'No blanks allowed';
                break;
            }
        }

        if(!$this->isValidEmail($inputs['email']))
        {
            $errors[] ='Must have a valid email';
        }

        if(!$this->isNumeric($inputs['pnumber']))
        {
            $errors[] ='Phone number must contain only digits';
        }

        if(!$this->hasMimLength(7,$inputs['pnumber']))
        {
            $errors[] ='Phone number must have a least 7 numbers';
        }

        if($this->hasMimLength(11,$inputs['pnumber']))
        {
            $errors[] ='Phone number cannot have more than 11 numbers';
        }

        if(!$this->isDateValid($inputs['date']))
        {
            $errors[] = 'Must have a valid date';
        }

        if(!$this->hasTwentyFourHoursPassed($inputs['date']))
        {
            $errors[] = 'Appointment need to be made 24 hours in advance';
        }
        return $errors;
    }

    protected function isDateValid($input)
    {
        $dt = explode('/',$input);
        if(checkdate($dt[0],$dt[1],$dt[2]))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function hasTwentyFourHoursPassed($input)
    {
        $dt = Carbon::now('America/New_York')->addDay(1);
        if($dt->format('m/d/Y') == $input)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function isNumeric($input)
    {
        if(is_numeric($input))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function isNotEmpty($input)
    {
        if($input != '')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function isValidEmail($input)
    {
        if (filter_var($input, FILTER_VALIDATE_EMAIL))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function hasMimLength($minLength,$input)
    {
        if(strlen($input)>= $minLength)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function viewAppointmentForm(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $crsf = bin2hex(random_bytes(10));
        $_SESSION['crsf'] = $crsf;
        return $this->container['view']->render($response, 'appointment.twig', array(
            "title" => "Appointment",
            'crsf' => $crsf
        ));
    }

    public function appointmentFormConfirmation(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $inputs = [
            'f_name' => $request->getParsedBody()['fname'],
            'l_name' => $request->getParsedBody()['lname'],
            'pnumber' => $request->getParsedBody()['pnumber'],
            'email' => $request->getParsedBody()['email'],
            'date' => $request->getParsedBody()['datepicker'],
            'title' => 'appointment',
        ];
        $errors = $this->validation($inputs);
        if($errors != [])
        {
            return $this->container['view']->render($response, 'errors.twig', array(
                "title" => "Appointment Errors",
                "errors" => $errors
            ));
        }
        {
            $mail = new PHPMailer;

            //Tell PHPMailer to use SMTP
            $mail->isSMTP();
            $mail->SMTPOptions = array( 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true ) );
            //Enable SMTP debugging
            // 0 = off (for production use)
            // 1 = client messages
            // 2 = client and server messages
            $mail->SMTPDebug = 0;

            $mail->Host = 'smtp.gmail.com';
            //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
            $mail->Port = 587;
            //Set the encryption system to use - ssl (deprecated) or tls
            $mail->SMTPSecure = 'tls';
            //Whether to use SMTP authentication
            $mail->SMTPAuth = true;
            //Username to use for SMTP authentication - use full email address for gmail
            $mail->Username = "AbrokwahS@gmail.com";
            //Password to use for SMTP authentication
            $mail->Password = "StarWars";
            //Set who the message is to be sent from
            $mail->setFrom('AbrokwahS@gmail.com', 'FCMC contact email');
            //Set who the message is to be sent to
            $mail->addAddress('glory.abrokwah@gmail.com');
            //Set the subject line
            $mail->Subject = $inputs['title'];
            $mail->msgHTML('From: '.$inputs['f_name'].' '.$inputs['l_name'].' '.$inputs['title'].'. Phone Number: '.$inputs['pnumber'].'<br>'.$inputs['email'].'<br> Appointment date :'.$inputs['date']);

            if (!$mail->send())
            {
                return $this->container['view']->render($response, 'errors.twig', array(
                    "title" => "Appointment Errors",
                    "errors" => ['Appointment could not be sent']
                ));
            }
            else
            {
                return $this->container['view']->render($response, 'success.twig', array(
                    "title" => "Success",
                ));
            }
        }
    }
}