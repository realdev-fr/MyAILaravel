<?php

namespace App\Services;


use App\Contracts\MCPServiceInterface;

class MailerExample
{
    private MCPServiceInterface $mcpService;

    public function __construct(MCPServiceInterface $mcpService)
    {
        $this->mcpService = $mcpService;
    }

    /**
     * This function send an email
     */
    public function sendMail(string $to_email, string $subject, string $body): string
    {
        try {
            $response = $this->mcpService->callTool('send_email', [
                'to_email' => $to_email,
                'subject' => $subject,
                'body' => $body,
            ]);

            // Return a user-friendly string instead of raw array
            if (isset($response['error'])) {
                return "Failed to send mail";
            } else {
                return "Mail sent successfully";
            }
        } catch (\Exception $e) {

            return "Error sending mail : " . $e->getMessage();
        }
    }
}
