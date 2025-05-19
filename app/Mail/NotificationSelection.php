<?php

namespace App\Mail;

use App\Models\Etudiant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationSelection extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * L'étudiant qui a été sélectionné
     *
     * @var \App\Models\Etudiant
     */
    public $etudiant;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Etudiant $etudiant
     * @return void
     */
    public function __construct(Etudiant $etudiant)
    {
        $this->etudiant = $etudiant;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Notification de sélection universitaire')
                    ->markdown('emails.selection-notification')
                    ->with([
                        'etudiant' => $this->etudiant,
                        'niveau' => $this->etudiant->niveau,
                        'filiere' => $this->etudiant->filiereSelectionnee
                    ]);
    }
}
