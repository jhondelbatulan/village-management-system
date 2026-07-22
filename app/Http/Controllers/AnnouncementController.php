<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest\StoreAnnouncementRequest;
use App\Http\Requests\AnnouncementRequest\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Models\Village;
use App\Models\Household;
use App\Services\AnnouncementService;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(
        private AnnouncementService $announcementService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        $announcements = $this->announcementService->getAnnouncementsForUser(
            $user,
            request('search'),
            request('status'),
            request('category'),
            request('type'),
            request('sort')
        );

        $village = $user->village_id ? Village::find($user->village_id) : null;
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();

        $categories = $this->getCategories();
        $types = $this->getTypes();

        return view('announcements.index', compact('announcements', 'village', 'villages', 'categories', 'types', 'role'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;
        $villageId = $user->village_id;

        $village = $villageId ? Village::find($villageId) : null;
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();

        $categories = $this->getCategories();
        $types = $this->getTypes();
        $households = $villageId ? Household::where('village_id', $villageId)->where('status', 'active')->orderBy('household_number')->get() : collect();

        return view('announcements.create', compact('village', 'villages', 'categories', 'types', 'role', 'households'));
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = auth()->user();
        $role = $user->role?->role_slug;

        if ($role === 'treasurer') {
            $allowedTypes = ['payment_reminder', 'general'];
            if (!in_array($data['announcement_type'] ?? 'general', $allowedTypes)) {
                return back()->withErrors(['announcement_type' => 'Treasurers can only create payment-related announcements.'])->withInput();
            }
        }

        if ($role !== 'super_admin' && !$data['village_id']) {
            $data['village_id'] = $user->village_id;
        }

        $announcement = $this->announcementService->create($data);

        AuditLogService::log('Create Announcement', 'Announcements', "Created announcement: {$announcement->title}");

        return redirect()->route('announcements.index')->with('success', 'Announcement created successfully.');
    }

    public function show(Announcement $announcement): View
    {
        $announcement->load('village', 'creator', 'responses.resident', 'responses.household');

        $role = auth()->user()->role?->role_slug;

        return view('announcements.show', compact('announcement', 'role'));
    }

    public function edit(Announcement $announcement): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;
        $villageId = $announcement->village_id;

        $village = Village::find($villageId);
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();
        $categories = $this->getCategories();
        $types = $this->getTypes();
        $households = $villageId ? Household::where('village_id', $villageId)->where('status', 'active')->orderBy('household_number')->get() : collect();

        return view('announcements.edit', compact('announcement', 'village', 'villages', 'categories', 'types', 'role', 'households'));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $data = $request->validated();

        $user = auth()->user();
        $role = $user->role?->role_slug;

        if ($role === 'treasurer') {
            $allowedTypes = ['payment_reminder', 'general'];
            if (!in_array($data['announcement_type'] ?? 'general', $allowedTypes)) {
                return back()->withErrors(['announcement_type' => 'Treasurers can only create payment-related announcements.'])->withInput();
            }
        }

        $announcement = $this->announcementService->update($announcement, $data);

        AuditLogService::log('Update Announcement', 'Announcements', "Updated announcement: {$announcement->title}");

        return redirect()->route('announcements.index')->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $title = $announcement->title;
        $this->announcementService->delete($announcement);

        AuditLogService::log('Delete Announcement', 'Announcements', "Deleted announcement: {$title}");

        return redirect()->route('announcements.index')->with('success', "Announcement \"{$title}\" has been deleted.");
    }

    public function publish(Announcement $announcement): RedirectResponse
    {
        $this->announcementService->publish($announcement);

        AuditLogService::log('Publish Announcement', 'Announcements', "Published announcement: {$announcement->title}");

        return redirect()->route('announcements.index')->with('success', 'Announcement published successfully.');
    }

    public function archive(Announcement $announcement): RedirectResponse
    {
        $this->announcementService->archive($announcement);

        AuditLogService::log('Archive Announcement', 'Announcements', "Archived announcement: {$announcement->title}");

        return redirect()->route('announcements.index')->with('success', 'Announcement archived successfully.');
    }

    public function respond(Announcement $announcement, Request $request): RedirectResponse
    {
        $request->validate([
            'response' => 'required|in:going,maybe,not_going',
        ]);

        $user = auth()->user();
        $resident = $user->household?->residents()->first();

        if (!$resident) {
            return back()->with('error', 'No resident profile linked to your account.');
        }

        $announcement->responses()->updateOrCreate(
            ['resident_id' => $resident->id],
            [
                'response' => $request->input('response'),
                'note' => $request->input('note'),
                'household_id' => $resident->household_id,
            ]
        );

        AuditLogService::log('Announcement Response', 'Announcements', "Responded to announcement: {$announcement->title} - {$request->input('response')}");

        return redirect()->route('announcements.show', $announcement)->with('success', 'Your response has been recorded.');
    }

    private function getCategories(): array
    {
        return ['General', 'Community', 'Health & Safety', 'Infrastructure', 'Events', 'Emergency', 'Government', 'Education', 'Environment', 'Other'];
    }

    private function getTypes(): array
    {
        return [
            'general' => 'General Announcement',
            'payment_reminder' => 'Payment Reminder',
            'emergency' => 'Emergency Advisory',
            'meeting' => 'Meeting Notice',
            'community' => 'Community Activity',
            'health' => 'Health Program',
            'disaster' => 'Disaster Warning',
            'training' => 'Training',
            'sports' => 'Sports',
            'attendance_required' => 'Attendance Required',
            'others' => 'Others',
        ];
    }
}
