<?php

namespace App\Mail;

/**
 * The regular notification email with the signed agreement attached as a PDF.
 */
class AgreementSigned extends Common
{
    public function build()
    {
        return parent::build()->attachData($this->data['pdf'], $this->data['pdf_name'], ['mime' => 'application/pdf']);
    }
}
