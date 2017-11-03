<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class QpCreateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          Schema::create('qp_modifier_summary_v', function (Blueprint $table) {
            $table->integer('list_line_id');
            $table->timestamps();
            $table->integer('list_header_id');
            $table->string('list_line_type_code',30);
            $table->string('automatic_flag',1);
            $table->string('modifier_level_code',30);
            $table->integer('list_price');
            $table->string('list_price_uom_code,3');
            $table->string('primary_uom_flag',1);
            $table->integer('inventory_item_id');
            $table->integer('organization_id');
            $table->integer('operand');
            $table->string('arithmetic_operator',30);
            $table->string('override_flag',1);
            $table->string('print_on_invoice_flag',1);
            $table->DATE('start_date_active');
            $table->DATE('end_date_active');
            $table->string('incompatibility_grp_code',30);
            $table->string('list_line_no',30);
            $table->integer('product_precedence');
            $table->integer('pricing_phase_id');
            $table->integer('pricing_attribute_id');
            $table->string('pricing_attribute_context',30);
            $table->string('product_attr',30);
            $table->string('product_attr_val',240);
            $table->string('product_uom_code',3);
            $table->string('comparison_operator_code',30);
            $table->string('pricing_attribute_context',30);
            $table->string('pricing_attr',30);
            $table->string('pricing_attr_value_from',240);
            $table->string('pricing_attr_value_to',240);
            $table->string('pricing_attribute_datatype',30);
            $table->string('product_attribute_datatype',30);

          });

          Schema::create('qp_qualifiers_v', function (Blueprint $table) {
            $table->integer('qualifier_id');
            $table->timestamps();
            $table->string('excluder_flag',1);
            $table->string('comparison_operator_code',30);
            $table->string('qualifier_context',30);
            $table->string('qualifier_attribute',30);
            $table->integer('qualifier_id');
            $table->integer('qualifier_grouping_no');
            $table->string('qualifier_attr_value',240);
            $table->integer('list_header_id');
            $table->integer('list_line_id');
            $table->date('start_date_active');
            $table->date('end_date_Active');
            $table->string('qualifier_datatype',10);
            $table->integer('qualifier_precendence');
          });

          Schema::create('QP_PRICING_ATTR_GET_V', function (Blueprint $table) {
            $table->integer('pricing_attribute_id');
            $table->timestamps();
            $table->integer('list_line_id');
            $table->string('excluder_flag',1);
            $table->string('product_attribute_context',30);
            $table->string('product_attribute',30);
            $table->string('product_attr_value',240);
            $table->string('product_uom_code',3);
            $table->string('pricing_attribute_datatype',30);
            $table->string('product_attribute_datatype',30);
            $table->integer('list_header_id');
            $table->string('list_line_no',30);
            $table->string('list_line_type_code',30);
            $table->string('arithmetic_operator',30);
            $table->integer('operand');
            $table->integer('benefit_limit');
            $table->string('benefit_uom_code',3);
            $table->string('automatic_flag',1);
            $table->string('modifier_level_code',30);
            $table->integer('pricing_phase_id');
            $table->integer('benefit_price_list_line_id');
            $table->integer('benefit_qty');
            $table->string('override_flag',1);
            $table->string('rltd_modifier_grp_type',30);
            $table->integer('rltd_modifier_id');
            $table->integer('rltd_modifier_grp_no');
            $table->integer('parent_list_line_id');
            $table->integer('to_rltd_modifier_id');
          });

          Schema::create('QP_PRICING_ATTR_V', function (Blueprint $table) {
            $table->integer('pricing_attribute_id');
            $table->timestamps();
            $table->integer('list_line_id');
            $table->string('excluder_flag',1);
            $table->string('product_attribute_context',30);
            $table->string('product_attribute',30);
            $table->string('product_attr_value_from',240);
            $table->string('product_uom_code',3);
            $table->string('pricing_attribute_context',30);
            $table->string('pricing_attr',30);
            $table->string('pricing_attr_value_from',240);
            $table->string('pricing_attr_value_to',240);
            $table->integer('attribute_grouping_no');
            $table->string('list_line_type_code',240);
            $table->string('arithmetic_operator',30);
            $table->integer('operand');
            $table->integer('estim_accrual_rate');
            $table->string('comparison_operator_code',30);
            $table->integer('list_header_id');
            $table->integer('parent_list_line_id');
            $table->integer('rltd_modifier_grp_no');
            $table->string('rltd_modifier_grp_type',30);
            $table->integer('rltd_modifier_id');
            $table->integer('to_rltd_modifier_id');
            $table->string('pricing_attribute_datatype',30);
            $table->string('product_attribute_datatype',30);
          });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('QP_PRICING_ATTR_V');
        Schema::dropIfExists('QP_PRICING_ATTR_GET_V');
        Schema::dropIfExists('qp_qualifiers_v');
        Schema::dropIfExists('qp_modifier_summary_v');        
    }
}
