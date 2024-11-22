<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\ServiceUser;
use App\Services\OpenRemoteService;
use Illuminate\Support\Facades\Auth;

class ServiceUserController extends Controller
{
    protected $client;
    protected $openRemoteService;

    public function __construct(OpenRemoteService $openRemoteService)
    {
        $this->client = new Client([
            'base_uri' => 'https://202.10.41.74:8443', // Base URI for the OpenRemote API
            'verify' => false, // Only for local development, disable SSL verification
        ]);
        $this->openRemoteService = $openRemoteService;
    }

    public function index(Request $request)
    {
        $token = $this->openRemoteService->refreshTokenIfNeeded();

        try {
            // Fetch service users associated with the current logged-in user
            $userServiceIds = ServiceUser::where('user_id', Auth::id())->pluck('serviceuser_id')->toArray();

            $response = $this->client->post('api/master/user/query', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => new \stdClass() // Empty object for the POST body
            ]);

            $users = json_decode($response->getBody()->getContents(), true);

            // Filter users to show only service accounts associated with the current user
            $filteredUsers = array_filter($users, function ($user) use ($userServiceIds) {
                return in_array($user['id'], $userServiceIds);
            });

            // Convert timestamps to human-readable dates
            foreach ($filteredUsers as &$user) {
                if (isset($user['createdOn'])) {
                    $user['createdOn'] = Carbon::createFromTimestampMs($user['createdOn'])->setTimezone('Asia/Jakarta')->toDateTimeString();
                }
            }

            return view('service-users.index', ['users' => $filteredUsers]);
        } catch (\Exception $e) {
            return redirect()->route('service-users.index')->with('error', 'Failed to fetch service users: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $token = $this->openRemoteService->refreshTokenIfNeeded();

        try {
            // Create a new service user via API
            $response = $this->client->post('api/master/user/master/users', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'enabled' => true,
                    'createdOn' => now()->toISOString(),
                    'serviceAccount' => true,
                    'username' => $request->input('username'),
                ],
            ]);

            $user = json_decode($response->getBody()->getContents(), true);
            $userId = $user['id'];

            // Assign roles to the new service user
            $roles = [
                [
                    "id" => "358a9437-cf1f-4c2e-b570-a27ebff306b3",
                    "name" => "read:admin",
                    "description" => "Read system settings, realms, and users",
                    "composite" => false,
                    "assigned" => true
                ],
                [
                    "id" => "a4ccb760-a147-42f7-8836-77a86bb96293",
                    "name" => "read",
                    "description" => "Read all data",
                    "composite" => true,
                    "assigned" => true
                ],
                [
                    "id" => "0cd17a0a-9559-4f23-a44b-3c7171053892",
                    "name" => "write:assets",
                    "description" => "Write asset data",
                    "composite" => false,
                    "assigned" => true
                ],
                [
                    "id" => "3cfe26ad-32a3-4180-91f5-8bfb4247b0a3",
                    "name" => "read:users",
                    "description" => "Read limited set of user details for use in rules etc.",
                    "composite" => false,
                    "assigned" => true
                ],
                [
                    "id" => "a9732894-6ca0-4cc4-8bd6-dd59bef2183f",
                    "name" => "write:rules",
                    "description" => "Write rulesets (NOTE: effectively super-user access!)",
                    "composite" => false,
                    "assigned" => true
                ],
                [
                    "id" => "9f47b1a9-e851-46b9-ade3-96e65d684f67",
                    "name" => "write:attributes",
                    "description" => "Write attribute data",
                    "composite" => false,
                    "assigned" => true
                ],
                [
                    "id" => "f3ab39e1-9878-49a7-b53a-e1fdedb9eee3",
                    "name" => "write:user",
                    "description" => "Write data of the authenticated user",
                    "composite" => false,
                    "assigned" => true
                ],
                [
                    "id" => "91c671f9-a80a-4c65-bb07-52025174e48a",
                    "name" => "read:logs",
                    "description" => "Read logs and log settings",
                    "composite" => false,
                    "assigned" => true
                ],
                [
                    "id" => "63a30db9-f52b-4064-88a7-a280e1974b9f",
                    "name" => "read:rules",
                    "description" => "Read rulesets",
                    "composite" => false,
                    "assigned" => true
                ],
                [
                    "id" => "65edd4dc-069b-4528-bafa-2fba38af43d6",
                    "name" => "write:admin",
                    "description" => "Write system settings, realms, and users",
                    "composite" => false,
                    "assigned" => true
                ],
                [
                    "id" => "f3c9b975-24de-42c3-aa63-1b3e6e9c249c",
                    "name" => "write:insights",
                    "description" => "Write dashboard data",
                    "composite" => false,
                    "assigned" => true
                ],
                [
                    "id" => "5a610ec5-bb07-420e-bff6-29975c4808eb",
                    "name" => "write:logs",
                    "description" => "Write log settings",
                    "composite" => false,
                    "assigned" => true
                ],
                [
                    "id" => "601c8f02-d950-4674-aecf-411cd4d8666c",
                    "name" => "read:insights",
                    "description" => "Read dashboards",
                    "composite" => false,
                    "assigned" => true
                ],
                [
                    "id" => "0ff84752-3b92-477c-9c4c-741bdd9464fb",
                    "name" => "read:map",
                    "description" => "View map",
                    "composite" => false,
                    "assigned" => true
                ],
                [
                    "id" => "f9e20510-c98c-4e24-90da-25bd69e93dff",
                    "name" => "read:assets",
                    "description" => "Read asset data",
                    "composite" => false,
                    "assigned" => true
                ],
                [
                    "id" => "c3a241a0-c90c-40e4-b3e3-09abd2653217",
                    "name" => "write",
                    "description" => "Write all data",
                    "composite" => true,
                    "assigned" => true
                ]
            ];

            $this->client->put("api/master/user/master/userRoles/{$userId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $roles,
            ]);

            // Store the relationship between the current user and the service user
            ServiceUser::create([
                'user_id' => Auth::id(),
                'serviceuser_id' => $userId,
            ]);

            return redirect()->route('service-users.index')->with('success', 'Service user created successfully.');
        } catch (\Exception $e) {
            return redirect()->route('service-users.index')->with('error', 'Failed to create service user: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $token = $this->openRemoteService->refreshTokenIfNeeded();

        try {
            // Delete a service user via API
            $response = $this->client->delete("api/master/user/master/users/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);

            // Delete the relationship record from the database
            ServiceUser::where('serviceuser_id', $id)->delete();

            return redirect()->route('service-users.index')->with('success', 'Service user deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('service-users.index')->with('error', 'Failed to delete service user: ' . $e->getMessage());
        }
    }
}
