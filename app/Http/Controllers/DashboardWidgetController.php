<?php

namespace App\Http\Controllers;

use App\Models\UserDashboardWidget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardWidgetController extends Controller
{
    /**
     * Show widget selection page
     */
    public function selectWidgets()
    {
        $availableWidgets = UserDashboardWidget::getAvailableWidgets();

        // Get user's currently selected widgets
        $selectedWidgets = Auth::user()
            ->dashboardWidgets()
            ->pluck('widget_key')
            ->toArray();

        return view('dashboard.select-widgets', [
            'availableWidgets' => $availableWidgets,
            'selectedWidgets' => $selectedWidgets,
        ]);
    }

    /**
     * Save user's widget selection
     */
    public function saveWidgets(Request $request)
    {
        $request->validate([
            'widgets' => 'required|array|min:1',
            'widgets.*' => 'string|distinct|in:actions_per_month,costs_per_month,costs_per_employee,actions_by_type',
        ]);

        $userId = Auth::id();
        $widgets = $request->get('widgets', []);

        // Use database transaction to ensure atomicity
        DB::transaction(function () use ($userId, $widgets) {
            // Delete existing widgets for this user
            UserDashboardWidget::where('user_id', $userId)->delete();

            // Insert new widgets with order
            foreach ($widgets as $index => $widgetKey) {
                UserDashboardWidget::create([
                    'user_id' => $userId,
                    'widget_key' => $widgetKey,
                    'order' => $index,
                ]);
            }
        });

        return redirect()->route('dashboard')->with('success', 'Widgets bijgewerkt!');
    }

    /**
     * Check if user has selected widgets
     */
    public function hasWidgets()
    {
        $count = Auth::user()
            ->dashboardWidgets()
            ->count();

        return response()->json(['has_widgets' => $count > 0]);
    }

    /**
     * Show widgets overview page
     */
    public function widgetsOverview()
    {
        $availableWidgets = UserDashboardWidget::getAvailableWidgets();

        // Get user's currently selected widgets
        $activeWidgets = Auth::user()
            ->dashboardWidgets()
            ->pluck('widget_key')
            ->toArray();

        return view('dashboard.widgets-overview', [
            'widgets' => $availableWidgets,
            'activeWidgets' => $activeWidgets,
        ]);
    }
}
