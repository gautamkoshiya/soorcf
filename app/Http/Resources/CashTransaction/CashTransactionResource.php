<?php

namespace App\Http\Resources\CashTransaction;

use Illuminate\Http\Resources\Json\JsonResource;

class CashTransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Reference' => $this->Reference,
            'createdDate' => $this->createdDate,
            'Type' => $this->Type,
            'Details' => $this->Details,
            'Debit' => $this->Debit,
            'Credit' => $this->Credit,
            'Differentiate' => $this->Differentiate,
        ];
    }
}
