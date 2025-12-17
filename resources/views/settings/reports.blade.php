@extends('layouts.app')
@section('content')
<form method="POST" class="report-schedule-form">
    @csrf

    <div class="form-group">
        <label for="day">Day</label>
        <select name="day" id="day" required>
            <option value="*">Every day</option>
            <option value="0">Sunday</option>
            <option value="1">Monday</option>
            <option value="2">Tuesday</option>
            <option value="3">Wednesday</option>
            <option value="4">Thursday</option>
            <option value="5">Friday</option>
            <option value="6">Saturday</option>
        </select>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="hour">Hour</label>
            <select name="hour" id="hour" required>
                @for ($i = 0; $i < 24; $i++)
                    <option value="{{ $i }}">
                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="form-group">
            <label for="minute">Minute</label>
            <select name="minute" id="minute" required>
                @for ($i = 0; $i < 60; $i++)
                    <option value="{{ $i }}">
                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                    </option>
                @endfor
            </select>
        </div>
    </div>

    <button type="submit" class="btn-primary">Save Schedule</button>
</form>


<style>
.report-schedule-form {
    max-width: 600px;
    background: #ffffff;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-left: 155px;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

.form-row {
    display: flex;
    gap: 12px;
}

label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #374151;
}

select {
    padding: 8px 10px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    font-size: 14px;
}

select:focus {
    outline: none;
    border-color: #2563eb;
}

.btn-primary {
    margin-top: 10px;
    width: 100%;
    background: #2563eb;
    color: #fff;
    padding: 10px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
}

.btn-primary:hover {
    background: #1e40af;
}

    </style>

@endsection