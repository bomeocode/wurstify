<?php

namespace App\Controllers;

// Wir erben vom mächtigeren ResourceController
use CodeIgniter\RESTful\ResourceController;
use App\Models\VendorModel;
use App\Models\VendorClaimModel;

// Die Klasse erbt jetzt von ResourceController
class ClaimController extends ResourceController
{
    public function showForm($vendorUuid)
    {
        $vendorModel = new VendorModel();
        $vendor = $vendorModel->where('uuid', $vendorUuid)->first();

        if (!$vendor) {
            // Dies ist nur eine Fallback-Anzeige, falls die URL direkt aufgerufen wird
            return 'Anbieter nicht gefunden.';
        }
        return view('claim/form', ['vendor' => $vendor]);
    }

    public function submit()
    {
        // $postData = $this->request->getPost();
        // header('Content-Type: application/json');
        // echo json_encode(['debug_data' => $postData]);
        // exit();

        $rules = [
            'vendor_uuid'   => 'required|string',
            'claimant_name' => 'required|string|max_length[255]',
            'contact_email' => 'required|valid_email',
            'proof_text'    => 'required|min_length[20]',
        ];
        $messages = [
            'proof_text' => ['min_length' => 'Bitte beschreiben Sie Ihren Nachweis etwas ausführlicher (mind. 20 Zeichen).'],
            'contact_email' => ['valid_email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.']
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $vendorModel = new VendorModel();
        $vendor = $vendorModel->where('uuid', $this->request->getPost('vendor_uuid'))->first();
        if (!$vendor) {
            return $this->failNotFound('Der zugehörige Anbieter konnte nicht gefunden werden.');
        }

        $claimModel = new VendorClaimModel();
        $claimData = [
            'vendor_id'     => $vendor['id'],
            'user_id'       => auth()->id(),
            'claimant_name' => $this->request->getPost('claimant_name'),
            'contact_email' => $this->request->getPost('contact_email'),
            'proof_text'    => $this->request->getPost('proof_text'),
            'ip_address'    => $this->request->getIPAddress(),
            'user_agent'    => (string) $this->request->getUserAgent(),
        ];

        if ($claimModel->save($claimData)) {
            // E-Mail senden
            $email = \Config\Services::email();
            $email->setTo('info@wurstify.com');
            $email->setSubject('Neuer Inhaber-Anspruch für: ' . $vendor['name']);
            $viewData = array_merge($claimData, ['vendor' => $vendor]);
            $message = view('emails/claim_notification', $viewData);
            $email->setMessage($message);
            $email->send(false);

            return $this->respondCreated(['message' => 'Vielen Dank! Ihr Anspruch wurde übermittelt und wird von uns geprüft.']);
        }

        return $this->failServerError('Ihr Anspruch konnte nicht gespeichert werden.');
    }
}
