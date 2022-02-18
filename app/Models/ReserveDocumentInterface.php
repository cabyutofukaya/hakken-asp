<?php

namespace App\Models;

interface ReserveDocumentInterface
{
    public function getParticipantIdsAttribute($value): array;

    public function setParticipantIdsAttribute($value);

    public function getOptionPricesAttribute($value): ?array;

    public function setOptionPricesAttribute($value);

    public function getAirticketPricesAttribute($value): ?array;

    public function setAirticketPricesAttribute($value);

    public function getHotelPricesAttribute($value): ?array;

    public function setHotelPricesAttribute($value);

    public function getHotelInfoAttribute($value): ?array;

    public function setHotelInfoAttribute($value);

    public function getHotelContactsAttribute($value): ?array;

    public function setHotelContactsAttribute($value);
}
