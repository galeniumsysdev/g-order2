<?php
/**
 * created by WK Productions
 */

namespace App\Http\Controllers;

use App\DPLLog;
use App\DPLNo;
use App\DPLSuggestNo;
use App\OrgStructure;
use App\OutletDistributor;
use App\SoHeader;
use App\SoLine;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DPLController extends Controller {
	public function __construct() {
		$this->middleware('auth');
	}

	public function generateSuggestNoForm() {
		$user = Auth::User();
		$outlets = OutletDistributor::join('customers', 'customers.id', 'outlet_distributor.outlet_id')
			->get();

		$outlet_list = array('---Pilih---');
		$distributor_list = array('---Silakan Pilih Outlet---');

		foreach ($outlets as $key => $outlet) {
			$outlet_list[$outlet->id] = $outlet->customer_name;
		}

		return view('admin.dpl.genSuggestNo', array(
			'outlet_list' => $outlet_list,
			'distributor_list' => $distributor_list,
		));
	}

	public function generateExec(Request $request) {
		$token = "";
		do {
			$codeAlphanum = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
			$codeAlphanum .= "0123456789";

			mt_srand();

			for ($i = 0; $i < 8; $i++) {
				$token .= $codeAlphanum[mt_rand(0, 35)];
			}

			$checkSuggestNo = DPLSuggestNo::where('suggest_no', $token)->count();
		} while ($checkSuggestNo);

		$dplSuggestNo = new DPLSuggestNo;
		$dplSuggestNo->mr_id = Auth::User()->id;
		$dplSuggestNo->outlet_id = $request->outlet;
		$dplSuggestNo->distributor_id = $request->distributor;
		$dplSuggestNo->suggest_no = $token;
		$dplSuggestNo->save();

		\Session::flash('suggest_no', $token);

		return redirect('/dpl/suggestno/success');
	}

	public function generateSuccess() {
		if (\Session::has('suggest_no')) {
			return view('admin.dpl.genSuggestNoSuccess', array('suggest_no' => session('suggest_no')));
		} else {
			return redirect('/dpl/suggestno/form');
		}
	}

	public function suggestNoValidation($outlet_id, $suggest_no) {
		$check_dpl = DPLSuggestNo::where('outlet_id', $outlet_id)
			->where('suggest_no', $suggest_no)
			->count();

		if ($check_dpl) {
			return response()->json(array('valid' => true));
		} else {
			return response()->json(array('valid' => false));
		}

	}

	public function getDistributorList($outlet_id) {
		$distributors = OutletDistributor::join('customers', 'customers.id', 'outlet_distributor.distributor_id')
			->where('outlet_id', $outlet_id)
			->get();

		return response()->json($distributors);
	}

	public function inputDiscount($suggest_no) {
		$dpl = DPLSuggestNo::select('users.id as dpl_mr_id',
			'users.name as dpl_mr_name',
			'outlet.id as dpl_outlet_id',
			'outlet.customer_name as dpl_outlet_name',
			'dpl_suggest_no.suggest_no',
			'outlet_id',
			'notrx',
			'fill_in')
			->join('users', 'users.id', 'dpl_suggest_no.mr_id')
			->join('customers as outlet', 'outlet.id', 'dpl_suggest_no.outlet_id')
			->leftjoin('dpl_no', 'dpl_no.suggest_no', 'dpl_suggest_no.suggest_no')
			->where('dpl_suggest_no.suggest_no', $suggest_no)
			->where('active', 1)
			->first();

		if (!$dpl['fill_in']) {
			return redirect('/dpl/discount/approval/' . $suggest_no);
		}

		$header = DB::table('so_header_v as sh')
			->where('notrx', '=', $dpl['notrx'])->first();
		if (!$header) {
			return view('errors.403');
		}
		$lines = DB::table('so_lines_v')->where('header_id', '=', $header->id)->get();

		$user_dist = User::where('customer_id', '=', $header->distributor_id)->first();

		$distributors = OutletDistributor::join('customers', 'customers.id', 'outlet_distributor.distributor_id')
			->where('outlet_id', $dpl['outlet_id'])
			->get();

		$distributor_list = array();

		foreach ($distributors as $key => $distributor) {
			$distributor_list[$distributor->distributor_id] = $distributor->customer_name;
		}

		return view('admin.dpl.discountForm', compact('dpl', 'header', 'lines', 'distributor_list'));
	}

	public function discountSet(Request $request) {
		$discount = $request->discount;
		$discount_gpl = $request->discount_gpl;
		$bonus_gpl = $request->bonus_gpl;
		$suggest_no = $request->suggest_no;
		$distributor = $request->distributor;
		$notrx = $request->notrx;

		$so_header = SoHeader::where('notrx', $notrx)
			->update(array('distributor_id' => $distributor));

		foreach ($discount as $key => $disc) {
			$so_line = SoLine::where('line_id', $key)
				->update(array('discount' => ($disc ? $disc : 0),
					'discount_gpl' => ($discount_gpl[$key] ? $discount_gpl[$key] : 0),
					'bonus_gpl' => ($bonus_gpl[$key] ? $bonus_gpl[$key] : 0),
				));
		}

		$dpl = DPLSuggestNo::where('suggest_no', $suggest_no)
			->update(array('fill_in' => 0));

		$this->dplLog($suggest_no, 'Input Discount');
		//notif
		event(new App\Events\HelloPusherEvent('Discount telah diset'));

		return redirect('/dpl/list');
	}

	public function discountApprovalForm($suggest_no) {
		$dpl = DPLSuggestNo::select('users.id as dpl_mr_id',
			'users.name as dpl_mr_name',
			'outlet.id as dpl_outlet_id',
			'outlet.customer_name as dpl_outlet_name',
			'suggest_no',
			'notrx',
			'fill_in')
			->join('users', 'users.id', 'dpl_suggest_no.mr_id')
			->join('customers as outlet', 'outlet.id', 'dpl_suggest_no.outlet_id')
			->where('suggest_no', $suggest_no)
			->where('active', 1)
			->first();

		if ($dpl['fill_in']) {
			return redirect('/dpl/discount/form/' . $suggest_no);
		}

		$header = DB::table('so_header_v as sh')
			->where('notrx', '=', $dpl['notrx'])->first();
		if (!$header) {
			return view('errors.403');
		}
		$lines = DB::table('so_lines_v')->where('header_id', '=', $header->id)->get();

		$user_dist = User::where('customer_id', '=', $header->distributor_id)->first();

		$distributor_list = OutletDistributor::join('customers', 'customers.id', 'outlet_distributor.distributor_id')
			->where('outlet_id', $dpl['outlet_id'])
			->get();

		return view('admin.dpl.discountApprovalForm', compact('dpl', 'header', 'lines', 'distributor_list'));
	}

	public function discountApprovalSet(Request $request) {
		$suggest_no = $request->suggest_no;
		$action = $request->action;
		if ($action == 'Approve') {
			$approved_by = Auth::user()->id;
			$next_approver = OrgStructure::where('user_id', Auth::user()->id)->first();
			$dpl = DPLSuggestNo::where('suggest_no', $suggest_no)
				->update(array('approved_by' => $approved_by, 'next_approver' => ($next_approver) ? $next_approver->directsup_user_id : ''));
		} else {
			$approved_by = '';
			$dpl = DPLSuggestNo::where('suggest_no', $suggest_no)
				->update(array('approved_by' => $approved_by, 'fill_in' => 1));
		}

		$this->dplLog($suggest_no, $action);

		return redirect('/dpl/list');
	}

	public function dplLog($suggest_no, $type) {
		$log = new DPLLog;
		$log->suggest_no = $suggest_no;
		$log->type = $type;
		$log->done_by = Auth::user()->id;
		$log->save();
	}

	public function dplLogHistory($suggest_no) {
		$dpl = DPLLog::select('users.name', 'dpl_log.*')
			->join('users', 'users.id', 'dpl_log.done_by')
			->where('suggest_no', $suggest_no)
			->get();

		return view('admin.dpl.dplHistory', array('dpl' => $dpl));
	}

	public function dplList() {
		$dpl = DPLSuggestNo::select('mr.id as dpl_mr_id',
			'mr.name as dpl_mr_name',
			'outlet.id as dpl_outlet_id',
			'outlet.customer_name as dpl_outlet_name',
			'distributor.id as dpl_distributor_id',
			'distributor.customer_name as dpl_distributor_name',
			'approver.id as dpl_appr_id',
			'approver.name as dpl_appr_name',
			'dpl_suggest_no.suggest_no',
			'dpl_no',
			'fill_in',
			'next_approver')
			->join('users as mr', 'mr.id', 'dpl_suggest_no.mr_id')
			->join('customers as outlet', 'outlet.id', 'dpl_suggest_no.outlet_id')
			->leftJoin('users as approver', 'approver.id', 'dpl_suggest_no.approved_by')
			->leftJoin('dpl_no', 'dpl_no.suggest_no', 'dpl_suggest_no.suggest_no')
			->leftJoin('so_headers', 'so_headers.notrx', 'dpl_suggest_no.notrx')
			->leftjoin('customers as distributor', 'distributor.id', 'so_headers.distributor_id')
			->where('active', 1)
			->get();

		foreach ($dpl as $key => $list) {
			$allowed = DB::select('SELECT privilegeSuggestNo(?,?) AS allowed', [$list->suggest_no, Auth::user()->id]);

			$dpl[$key]->btn_discount = ($list->fill_in && $allowed[0]->allowed) ? '<a href="/dpl/discount/form/' . $list->suggest_no . '" class="btn btn-danger">Discount</a>' : '';

			$dpl[$key]->btn_confirm = (!$list->fill_in && $allowed[0]->allowed && $list->next_approver == Auth::user()->id) ? '<a href="/dpl/discount/approval/' . $list->suggest_no . '" class="btn btn-primary">Confirmation</a>' : '';

			$dpl[$key]->btn_dpl_no = (!$list->fill_in && empty($list->next_approver) && empty($list->dpl_no)) ? '<a href="/dpl/input/form/' . $list->suggest_no . '" class="btn btn-warning">DPL No.</a>' : '';
		}

		return view('admin.dpl.dplList', array('dpl' => $dpl));
	}

	public function dplNoInputForm($suggest_no) {
		$dpl = DPLSuggestNo::select('users.id as dpl_mr_id',
			'users.name as dpl_mr_name',
			'outlet.id as dpl_outlet_id',
			'outlet.customer_name as dpl_outlet_name',
			'distributor.id as dpl_distributor_id',
			'distributor.customer_name as dpl_distributor_name',
			'dpl_suggest_no.suggest_no',
			'dpl_no')
			->join('users', 'users.id', 'dpl_suggest_no.mr_id')
			->join('customers as outlet', 'outlet.id', 'dpl_suggest_no.outlet_id')
			->join('customers as distributor', 'distributor.id', 'dpl_suggest_no.distributor_id')
			->leftjoin('dpl_no', 'dpl_no.suggest_no', 'dpl_suggest_no.suggest_no')
			->where('dpl_suggest_no.suggest_no', $suggest_no)
			->where('active', 1)
			->first();

		if (empty($dpl->dpl_no)) {
			$max_dpl_no = DPLNo::max('dpl_no');
			$readonly = '';

			if ($max_dpl_no) {
				$max_no = intval(substr($max_dpl_no, 6));
				$dpl_no = date('Ym') . str_pad($max_no + 1, 5, 0, STR_PAD_LEFT);
			} else {
				$dpl_no = date('Ym') . str_pad(1, 5, 0, STR_PAD_LEFT);
			}
		} else {
			$dpl_no = $dpl->dpl_no;
			$readonly = 'readonly';
		}

		return view('admin.dpl.dplNoForm', array('dpl' => $dpl, 'readonly' => $readonly, 'dpl_no' => $dpl_no));
	}

	public function dplNoInputSet(Request $request) {
		$suggest_no = $request->suggest_no;
		$dpl_no = $request->dpl_no;

		$this->dplLog($suggest_no, 'Input DPL No #' . $dpl_no);

		$check_dpl = DPLNo::where('dpl_no', $dpl_no)->count();

		if (!$check_dpl) {
			$dpl = new DPLNo;
			$dpl->dpl_no = $dpl_no;
			$dpl->suggest_no = $suggest_no;
			$dpl->save();

			print_r($dpl);
		} else {
			return redirect()->back()->withInput()->with('msg', 'DPL No #' . $dpl_no . ' already exist.');
		}
	}

}
