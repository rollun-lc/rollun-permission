<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\OAuth;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;

class RegisterMiddleware extends CredentialMiddleware
{
    const ACTION = 'register';

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->googleClient->setRedirectUri($this->actionToRedirectUri(self::ACTION));

        if ($this->getSession($request)->has(UserInterface::class)) {
            return $this->getAuthorizedResponse($request);
        }

        if (!$this->isOAuthAuthenticated($request)) {
            return $this->getUnAuthorizedResponse($request);
        };

        $credential = (string)$this->googleClient->getIdToken();

        if (!$this->userRepository->authenticate($credential)) {
            try {
                $this->sentRegistrationMail();
            } catch (\Throwable $e) {
                $this->logger->warning(
                    'Failed to send registration approve email',
                    ['exception' => $e]
                );
            }
        }

        return $this->getAuthorizedResponse($request);
    }

    /**
     * Send email to approve registration
     *
     * Email config example:
     *
     *  $config = [
     *      'email' => [
     *          'bodyTemplate' => 'Can except :email and :userId placeholders'
     *          'from' => 'from@email.com'
     *          'to' => 'to@email.com'
     *          'subject' => 'Important subject'
     *          'smtpOptions' => [
     *              'name' => 'google.example.name',
     *              'host' => 'google.example.host',
     *              'port' => 25
     *          ]
     *      ]
     *  ]
     *
     * @throws InvalidArgumentException
     */
    protected function sentRegistrationMail()
    {
        $emailConfig = $this->getConfig('email');

        foreach (['bodyTemplate', 'from', 'to', 'subject', 'smtpOptions'] as $option) {
            if (!isset($emailConfig[$option])) {
                throw new InvalidArgumentException("Invalid '{$option}' option for email config");
            }
        }

        $userId = $this->googleClient->getIdToken();
        $email = $this->googleClient->getUserEmail();

        $emailMessageTemplate = $emailConfig['bodyTemplate'];
        $emailMessage = str_replace([':email', ':userId'], [$email, $userId], $emailMessageTemplate);

        $mail = new Message();
        $mail->setBody($emailMessage);

        $mail->setFrom($emailConfig['from']);
        $mail->setSubject($emailConfig['subject']);
        $mail->addTo($emailConfig['to']);

        $transport = new Smtp();
        $options = new SmtpOptions($emailConfig['smtpOptions']);
        $transport->setOptions($options);

        $this->logger->debug(
            json_encode(
                [
                    'Email for registration',
                    'Email body: ' . $emailMessage,
                    'Email from: ' . $emailConfig['from'],
                    'Email subject: ' . $emailConfig['subject'],
                    'Email to: ' . $emailConfig['to'],
                    'Email SMTP options: ' . json_encode($emailConfig['smtpOptions']),
                ]
            )
        );

        $transport->send($mail);
    }
}
