<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;

class Cart
{
  public $items = null;
  public $totalQty = 0;
  public $totalPrice = 0;

  public function __construct($oldCart)
  {
    if ($oldCart){
      $this->items = $oldCart->items;
      $this->totalQty = $oldCart->totalQty;
      $this->totalPrice = $oldCart->totalPrice;
    }
  }

  public function add($item,$id,$qty,$uom,$price){
      $storedItem = ['qty'=>0, 'uom'=>'', 'price'=>$price, 'amount'=>0, 'item'=>$item];
      if ($this->items){//jika ada array
        if(array_key_exists($id.'-'.$uom, $this->items)){
          $storedItem = $this->items[$id.'-'.$uom];
          $this->totalQty--;
        }
      }
        $storedItem['qty']+=$qty; //total product
        $storedItem['uom']= $uom;
        $storedItem['price']=$price;
        $storedItem['amount']=$price* $storedItem['qty'];
        $this->items[$id.'-'.$uom] = $storedItem;
        $this->totalQty++;
        $this->totalPrice += $price*$qty;
  }

  public function removeItem($id){
      $this->totalQty-=1;
      $this->totalPrice -= $this->items[$id]['amount'] ;
      unset($this->items[$id]);
  }

  public function editItem($id,$qty,$uom,$itemid,$harga){
      if ($this->items){//jika ada array
        if(array_key_exists($id, $this->items)){
          $storedItem = $this->items[$id];

          if($storedItem['qty']!=$qty or $storedItem['uom']!=$uom or $storedItem['price']!=$harga )
          {
            $this->totalPrice -= $this->items[$id]['amount'];
            $storedItem['qty']=$qty; //total product
            $storedItem['uom']= $uom;
            $storedItem['price']=$harga;
            $storedItem['amount']=$harga* $qty;
            $this->items[$id] = $storedItem;
            $this->totalPrice +=   $storedItem['amount'];

          }
        }
      }
  }


}
