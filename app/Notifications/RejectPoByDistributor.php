<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\SoHeader;

class RejectPoByDistributor extends Notification
{
    use Queueable;
    public $header;
    public $is_distributor;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(SoHeader $h,  $is_dist)
    {
        $this->header = $h;
        $this->is_distributor=$is_dist;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','Database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
      if($this->is_distributor==1)
      {
        return (new MailMessage)
                    ->subject('Pembatalan Po: '.$this->header->customer_po)
                    ->line('Mohon maaf, bersama ini kami informasikan bahwa PO anda No: '.$this->header->customer_po.' telah dibatalkan.')
                    ->line('Silahkan konfirmasi ke Distributor untuk penjelasan lebih detail.');
      }else{
        return (new MailMessage)
                    ->subject('Pembatalan Po: '.$this->header->customer_po)
                    ->line('Bersama ini kami informaskikan bahwa PO No: '.$this->header->customer_po.' telah dibatalkan oleh customer.')
                    ->line('Silahkan konfirmasi ke customer untuk penjelasan lebih detail.');
      }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
     public function toDatabase($notifiable)
     {
       if($this->is_distributor==1){
         return [
           'tipe'=>'Pembatalan PO dari Distributor',
           'subject'=>'Pembatalan PO '.$this->header->customer_po,
           'order'=>$this->header
         ];
       }else{
         return [
           'tipe'=>'Pembatalan PO dari Customer',
           'subject'=>'Pembatalan PO '.$this->header->customer_po,
           'order'=>$this->header
         ];
       }

     }
}
