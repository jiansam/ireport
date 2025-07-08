<?php

namespace App\Utils;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
 

class Email extends Mailable
{
    use Queueable, SerializesModels;
    
    public $viewName;
    public $viewData;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( )
    { 
    }
    
    
    
    public function setView($viewName , $data) {
        $this->viewName = $viewName;
        $this->viewData = $data;
    }
  

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    { 
        if ($this->viewName) {
           return $this->view( $this->viewName)->with($this->viewData);
        } else {
           return null;
        }
    }
    
     
     
}
