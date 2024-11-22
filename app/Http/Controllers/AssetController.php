<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Services\OpenRemoteService;
use App\Models\UserAsset;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
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
        $userAssetIds = UserAsset::where('user_id', Auth::id())->pluck('asset_id')->toArray();

        try {
            $response = $this->client->post('api/master/asset/query', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    "recursive" => true,
                    "select" => ["basic" => true],
                    "access" => "PRIVATE",
                    "orderBy" => ["property" => "CREATED_ON", "descending" => true],
                    "limit" => 0
                ]
            ]);

            $assets = json_decode($response->getBody()->getContents(), true);

            // Filter assets to only include those associated with the logged-in user
            $filteredAssets = array_filter($assets, function ($asset) use ($userAssetIds) {
                return in_array($asset['id'], $userAssetIds);
            });

            // Convert timestamps to human-readable dates
            foreach ($filteredAssets as &$asset) {
                if (isset($asset['createdOn'])) {
                    $asset['createdOn'] = Carbon::createFromTimestampMs($asset['createdOn'])->setTimezone('Asia/Jakarta')->toDateTimeString();
                }
            }

            return view('assets.index', ['assets' => $filteredAssets]);
        } catch (\Exception $e) {
            return redirect()->route('assets.index')->with('error', 'Failed to fetch assets: ' . $e->getMessage());
        }
    }

    public function deleteAsset(Request $request, $id)
    {
        $token = $this->openRemoteService->refreshTokenIfNeeded();

        try {
            $response = $this->client->delete("api/master/asset?assetId={$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);
            UserAsset::where('asset_id', $id)->delete();

            return redirect()->route('assets.index')->with('success', 'Asset deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('assets.index')->with('error', 'Failed to delete asset: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $token = $this->openRemoteService->refreshTokenIfNeeded();

        try {
            $response = $this->client->post('api/master/asset', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'version' => 0,
                    'createdOn' => 0,
                    'name' => $request->input('name'),
                    'accessPublicRead' => false,
                    'realm' => 'master',
                    'type' => 'ThingAsset',
                    'attributes' => [
                        'notes' => [
                            'name' => 'notes',
                            'type' => 'text',
                            'meta' => [
                                "storeDataPoints" => true
                                ],
                            'value' => null,
                            'timestamp' => 0
                        ],
                        'location' => [
                            'name' => 'location',
                            'type' => 'GEO_JSONPoint',
                            'value' => null,
                            'timestamp' => 0
                        ]
                    ]
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            $assetId = $responseData['id'];
            $assetName = $responseData['name'];

            // Store the user ID and asset ID in the user_assets table
            UserAsset::create([
                'user_id' => Auth::id(),
                'asset_id' => $assetId,
                'asset_name' => $assetName,
            ]);

            return redirect()->route('assets.index')->with('success', 'Asset created successfully!');
        } catch (\Exception $e) {
            return redirect()->route('assets.index')->with('error', 'Failed to create asset: ' . $e->getMessage());
        }
    }

    public function show(Request $request, $assetId)
    {
        $token = $this->openRemoteService->refreshTokenIfNeeded();

        try {
            $response = $this->client->get("/api/master/asset/{$assetId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);

            $asset = json_decode($response->getBody()->getContents(), true);

            // Convert timestamps to human-readable dates if necessary
            if (isset($asset['createdOn'])) {
                $asset['createdOn'] = Carbon::createFromTimestampMs($asset['createdOn'])->setTimezone('Asia/Jakarta')->toDateTimeString();
            }

            return view('assets.asset', ['asset' => $asset]);
        } catch (\Exception $e) {
            return redirect()->route('assets.index')->with('error', 'Failed to fetch asset: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $token = $this->openRemoteService->refreshTokenIfNeeded();

        try {
            $response = $this->client->get("/api/master/asset/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);

            $asset = json_decode($response->getBody()->getContents(), true);

            return view('assets.edit', ['asset' => $asset, 'id' => $id]);
        } catch (\Exception $e) {
            return redirect()->route('assets.index')->with('error', 'Failed to fetch asset details: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $token = $this->openRemoteService->refreshTokenIfNeeded();

        try {
            $response = $this->client->get("/api/master/asset/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);

            $asset = json_decode($response->getBody()->getContents(), true);

            $data = [
                'id' => $id,
                'version' => $asset['version'],
                'createdOn' => $asset['createdOn'],
                'name' => $request->input('name'),
                'accessPublicRead' => $asset['accessPublicRead'],
                'realm' => $asset['realm'],
                'type' => $asset['type'],
                'attributes' => $request->input('attributes')
            ];
            // Ensure all attributes have the required meta structure
            foreach ($data['attributes'] as &$attribute) {
                if (!isset($attribute['meta'])) {
                    $attribute['meta'] = [
                        'storeDataPoints' => true
                    ];
                } else {
                    $attribute['meta']['storeDataPoints'] = true;
                }
            }

            $updateResponse = $this->client->put("/api/master/asset/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $data
            ]);

            return redirect()->route('assets.show', ['id' => $id])->with('success', 'Asset updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('assets.index')->with('error', 'Failed to update asset: ' . $e->getMessage());
        }
    }
    public function updateAttribute(Request $request, $assetId)
{
    $token = $this->openRemoteService->refreshTokenIfNeeded();

    try {
        $data = $request->json()->all();

        $response = $this->client->put("api/master/asset/attributes", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $data
        ]);

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

}
