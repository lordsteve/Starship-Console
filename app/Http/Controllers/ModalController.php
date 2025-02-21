<?php

namespace App\Http\Controllers;

use App\Models\CargoItem;
use App\Models\Character;
use App\Models\Division;
use App\Models\Notification;
use App\Models\Starship;
use App\Models\System;
use App\Models\User;
use App\Notifications\Notify;
use App\Notifications\SendInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification as FacadesNotification;

class ModalController extends Controller
{
    public function success(Request $request)
    {
        return view('modals.success', [
            'message' => $request->message,
        ]);
    }

    public function register()
    {
        return view('modals.register');
    }

    public function login()
    {
        return view('modals.login');
    }

    public function roll($starship)
    {
        return view('modals.dice', ['starship' => $starship]);
    }

    public function newCharacter()
    {
        return view('modals.new-character');
    }

    public function editCharacter($id)
    {
        $character = Character::find($id)->load('starship');

        return view('modals.edit-character', [
            'character' => $character,
        ]);
    }

    public function deleteCharacter($id)
    {
        $message = 'Are you sure you want to delete this character? This is Un-Undoable!';
        $yesButton = true;

        return view('modals.delete-character', [
            'character' => $id,
            'message' => $message,
            'yesButton' => $yesButton
        ]);
    }

    public function newStarship()
    {
        return view('modals.new-starship');
    }

    public function editStarship($id)
    {
        $starship = Starship::find($id);
        $characters = Character::where('starship_id', $id)->get();

        return view('modals.edit-starship', compact('starship', 'characters'));
    }

    public function deleteStarship($id)
    {
        if (auth()->user()->starships->count() <= 1) {
            $message = 'You cannot delete your only starship.';
            $yesButton = false;
        }else{
            $message = 'Are you sure you want to delete this starship? This is Un-Undoable! If you are sure, then please select a new starship where the crew will be transferred to. If an appropriate transfer does not exist, please create a new starship first. By default, your crew will be transferred to:';
            $yesButton = true;
        }

        return view('modals.delete-starship', [
            'starship' => Starship::where('id', $id)->first(),
            'message' => $message,
            'yesButton' => $yesButton
        ]);
    }

    public function deleteSystem($id)
    {
        $message = 'Are you sure you want to delete this system? This is Un-Undoable!';
        $yesButton = true;

        return view('modals.delete-system', [
            'system' => System::where('id', $id)->first(),
            'message' => $message,
            'yesButton' => $yesButton
        ]);
    }

    public function crewManifest(Starship $starship)
    {
        $crew = Character::where('starship_id', $starship->id)->get();
        $divisions = $starship->divisions;
        $captain = Character::where('id', $starship->captain_id)->first();

        return view('modals.crew-manifest', compact('crew', 'divisions', 'captain'));
    }

    public function cargoManifest(Starship $starship)
    {
        $cargo = CargoItem::where('starship_id', $starship->id)->get();

        return view('modals.cargo-manifest', compact('cargo'));
    }

    public function addUser($email, Starship $starship)
    {
        $user = User::where('email', $email)->first();

        if ($user && $user->starships()->find($starship)) {
            $message = $user->name . ' is already aboard the ' . $starship->name . '. Make sure they have created a character and assigned it to the ' . $starship->name . '.';
        } else if ($user) {
            $user->starships()->attach($starship->id);
            $message = $user->name . ' has been brought aboard the ' . $starship->name . '!';
            $user->notify(new Notify(
                'You have been brought aboard the ' . $starship->name . '! Please visit your dashboard to assign a character to this starship.',
                '/dashboard',
                $user->id
            ));
        }else{
            $starship->notify(new SendInvitation($email, $starship->id));

            $message = "$email could not be found in our system, but an email has been sent to them to invite them aboard.";
        }

        $data = [
            'message' => $message,
        ];

        return $data;
    }

    public function addSystem(Starship $starship, Division $division)
    {
        return view('modals.new-system', compact('starship', 'division'));
    }

    public function editSystem(System $system)
    {
        return view('modals.edit-system', compact('system'));
    }

    public function forgotPassword()
    {
        return view('modals.forgot-password');
    }
}
