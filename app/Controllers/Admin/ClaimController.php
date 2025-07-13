<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VendorClaimModel;
use App\Models\VendorModel;

class ClaimController extends BaseController
{
    public function index()
    {
        $claimModel = new VendorClaimModel();
        $data = [
            'claims' => $claimModel
                ->select('vendor_claims.*, users.username, vendors.name as vendor_name')
                ->join('users', 'users.id = vendor_claims.user_id')
                ->join('vendors', 'vendors.id = vendor_claims.vendor_id')
                ->orderBy('created_at', 'DESC')
                ->paginate(20),
            'pager' => $claimModel->pager,
        ];
        return view('admin/claims/index', $data);
    }

    public function show($id = null)
    {
        $claimModel = new VendorClaimModel();
        $claim = $claimModel
            ->select('vendor_claims.*, users.username, vendors.name as vendor_name, vendors.address')
            ->join('users', 'users.id = vendor_claims.user_id')
            ->join('vendors', 'vendors.id = vendor_claims.vendor_id')
            ->find($id);

        if (!$claim) {
            return redirect()->to(route_to('admin_claims'))->with('error', 'Anspruch nicht gefunden.');
        }

        return view('admin/claims/show', ['claim' => $claim]);
    }

    public function process()
    {
        $claimId = $this->request->getPost('claim_id');
        $action = $this->request->getPost('action'); // 'approve' oder 'reject'

        $claimModel = new VendorClaimModel();
        $claim = $claimModel->find($claimId);

        if (!$claim || !in_array($action, ['approve', 'reject'])) {
            return redirect()->back()->with('error', 'Ungültige Aktion.');
        }

        if ($action === 'approve') {
            // 1. Vendor dem User zuweisen
            $vendorModel = new VendorModel();
            $vendorModel->update($claim['vendor_id'], ['owner_user_id' => $claim['user_id']]);

            // 2. Dem User die "vendor"-Rolle geben
            $user = auth()->getProvider()->findById($claim['user_id']);
            $user->addGroup('vendor');

            // 3. Den Status des Claims aktualisieren
            $claimModel->update($claimId, ['status' => 'approved']);

            return redirect()->to(route_to('admin_claims'))->with('message', 'Anspruch genehmigt und Inhaberschaft übertragen.');
        }

        if ($action === 'reject') {
            $claimModel->update($claimId, ['status' => 'rejected']);
            return redirect()->to(route_to('admin_claims'))->with('message', 'Anspruch wurde abgelehnt.');
        }
    }
}
