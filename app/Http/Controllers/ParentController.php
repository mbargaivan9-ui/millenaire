<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function dashboard()
    {
        $parent = auth()->user();
        
        // Get children attached to parent (via class_id relationship)
        $children = User::where('role', 'student')->get();
        
        $childrenWithData = $children->map(function($child) {
            return [
                'student' => $child,
                'class' => $child->class,
                'payments' => $child->payments,
                'absences' => $child->absences,
                'averageGrade' => $child->grades->avg('average'),
                'bulletin' => $child->bulletins->first(),
                'pendingPayments' => $child->payments->where('status', 'pending')->sum('amount')
            ];
        })->filter(function($data) {
            return $data['student'] !== null;
        });
        
        return view('parent.dashboard', [
            'children' => $childrenWithData,
            'totalPending' => $childrenWithData->sum('pendingPayments')
        ]);
    }
    
    public function childDetails(User $student)
    {
        // Authorization can be added via policy
        
        return view('parent.children.details', [
            'student' => $student,
            'grades' => $student->grades,
            'absences' => $student->absences,
            'payments' => $student->payments,
            'bulletins' => $student->bulletins,
            'class' => $student->class
        ]);
    }
    
    public function payments()
    {
        $parent = auth()->user();
        
        // Get all children's payments
        $children = User::where('role', 'student')->get();
        
        $allPayments = $children->flatMap(function($child) {
            return $child->payments->map(function($payment) use ($child) {
                $payment->student_name = $child->name;
                return $payment;
            });
        })->sortByDesc('created_at');
        
        return view('parent.payments.index', [
            'payments' => $allPayments,
            'summary' => [
                'total' => $allPayments->sum('amount'),
                'paid' => $allPayments->where('status', 'completed')->sum('amount'),
                'pending' => $allPayments->where('status', 'pending')->sum('amount'),
                'overdue' => $allPayments->where('status', 'overdue')->sum('amount')
            ]
        ]);
    }
    
    public function printBulletin(User $student)
    {
        $bulletin = $student->bulletins->first();
        
        return view('parent.bulletins.print', [
            'student' => $student,
            'bulletin' => $bulletin,
            'grades' => $student->grades
        ]);
    }
}
