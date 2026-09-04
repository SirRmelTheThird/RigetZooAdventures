<?php

namespace Requests;

class BookTicketRequest extends FormRequest
{
    protected function rules()
    {
        $adult = intval($this->get('adult', 0));
        $child = intval($this->get('child', 0));

        $this->validator
            ->required('date', $this->get('date'))
            ->date('date', $this->get('date'))
            ->futureDate('date', $this->get('date'));

        if ($adult <= 0 && $child <= 0) {
            $this->validator->required('tickets', '', 'Please select at least one ticket');
        }
    }

    public function getAdultCount(): int
    {
        return intval($this->get('adult', 0));
    }

    public function getChildCount(): int
    {
        return intval($this->get('child', 0));
    }

    public function getDate(): string
    {
        return $this->get('date');
    }
}
