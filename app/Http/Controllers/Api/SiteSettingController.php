<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSiteSettingsRequest;
use App\Http\Resources\SiteSettingResource;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteSettingController extends Controller
{
    public function index()
    {
        $site_settings = SiteSetting::all();
        return SiteSettingResource::collection($site_settings);
    }

    public function update(UpdateSiteSettingsRequest $request)
    {
        $request->validated();
    
        //Check image
        if ($request->hasFile('site_logo')) {
            $imageData = $this->uploadImage($request->file('site_logo'));
            SiteSetting::where('setting_key', 'site_logo')->update(['setting_value' => $imageData['url']]);
        }
    
        //Check other fields
        $settingsToUpdate = [
            'footer_email_info' => $request->input('footer_email_info'),
            'footer_address_info' => $request->input('footer_address_info'),
            'footer_phone_number' => $request->input('footer_phone_number'),
        ];
    
        //Update
        foreach ($settingsToUpdate as $settingKey => $settingValue) {
            if ($settingValue !== null) {
                SiteSetting::where('setting_key', $settingKey)->update(['setting_value' => $settingValue]);
            }
        }
    
        return response()->json(['message' => 'Settings updated successfully']);
    }
    
    private function uploadImage($image)
    {
        $im = $image->storeOnCloudinary('site-settings');
        return ['url' => $im->getSecurePath()];
    }
    

}
