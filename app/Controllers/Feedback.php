<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FeedbackModel;
use Config\Services;

class Feedback extends ResourceController
{
    public function index()
    {
        return view('feedback/form_content');
    }

    public function create()
    {
        $rules = ['feedback_text' => 'required|min_length[10]'];
        $messages = [
            'feedback_text' => [
                'required'   => 'Bitte geben Sie Ihr Feedback ein.',
                'min_length' => 'Ihr Feedback sollte mindestens 10 Zeichen lang sein.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $model = new FeedbackModel();
        $data = [
            'user_id'       => auth()->loggedIn() ? auth()->id() : null,
            'feedback_text' => $this->request->getPost('feedback_text'),
            'user_agent'    => (string) $this->request->getUserAgent(),
        ];

        try {
            if ($model->save($data)) {
                $email = Services::email();
                $email->setTo('info@wurstify.com');
                $email->setSubject('Neues Feedback auf Wurstify');
                $viewData = [
                    'username'      => auth()->loggedIn() ? auth()->user()->username : 'Gast',
                    'createdAt'     => date('d.m.Y H:i:s'),
                    'userAgent'     => $data['user_agent'],
                    'feedbackText'  => $data['feedback_text']
                ];
                $message = view('emails/feedback_notification', $viewData);
                $email->setMessage($message);

                if (!$email->send(false)) {
                    log_message('error', 'Feedback-E-Mail konnte nicht gesendet werden: ' . $email->printDebugger(['headers']));
                }

                return $this->respondCreated(['message' => 'Vielen Dank für Ihr Feedback!']);
            }
        } catch (\Exception $e) {
            log_message('error', '[Feedback] ' . $e->getMessage());
            return $this->failServerError('Das Feedback konnte aufgrund eines internen Fehlers nicht gespeichert werden.');
        }

        return $this->fail('Das Feedback konnte aus einem unbekannten Grund nicht gespeichert werden.');
    }
}
