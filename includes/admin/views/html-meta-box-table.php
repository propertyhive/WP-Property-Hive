<form id="posts-filter" method="get">

	<input type="hidden" name="post_status" class="post_status_page" value="all">
	<input type="hidden" name="post_type" class="post_type_page" value="key_date">

	<div class="inside">

		<input type="hidden" name="orderby" value="date_due"><input type="hidden" name="order" value="asc">

		<input type="hidden" name="post_status" class="post_status_page" value="all">
		<input type="hidden" name="post_type" class="post_type_page" value="key_date">


		<input type="hidden" id="_wpnonce" name="_wpnonce" value="70772481ef">
		<input type="hidden" name="_wp_http_referer" value="/wp-admin/edit.php?post_type=key_date&orderby=date_due&order=asc&status=upcoming_and_overdue&filter_action=Filter">
		<div class="tablenav top">

			<div class="alignleft actions bulkactions">
				<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk
					action</label><select name="action" id="bulk-action-selector-top">
					<option value="-1">Bulk actions</option>
					<option value="edit" class="hide-if-no-js">Edit</option>
					<option value="trash">Move to Bin</option>
				</select>
				<input type="submit" id="doaction" class="button action" value="Apply">
			</div>

			<div class="alignleft actions">

				<select name="_key_date_type_id">
					<option value="">All Types</option>
					<option value="83">Empty Property Check</option>
					<option value="84">Eoin's Birthday</option>
					<option value="80">Gas Safety Certificate</option>
					<option value="79">Inspection</option>
					<option value="81">Legionella Risk Assessment</option>
					<option value="77">Move In</option>
					<option value="78">Move Out</option>
				</select>

                <select name="status" id="dropdown_sale_status">
					<option value="">All Statuses</option>
					<option value="upcoming_and_overdue" selected="selected">Upcoming & Overdue</option>
					<option value="booked"> Booked</option>
					<option value="complete"> Complete</option>
					<option value="pending"> Pending</option>
				</select>

                <input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter">

            </div>
			<div class="tablenav-pages one-page">
                <span class="displaying-num">3 items</span>
				<span class="pagination-links">
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
                    <span class="paging-input">
                        <label for="current-page-selector" class="screen-reader-text">Current Page</label>
                        <input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging">
                        <span class="tablenav-paging-text">
                            of <span class="total-pages"> 1 </span>
                        </span>
                    </span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
                </span>
            </div>

            <br class="clear">

		</div>


		<h2 class="screen-reader-text">Posts list</h2>
		<table class="wp-list-table widefat fixed striped table-view-list posts" style="border-collapse: collapse;">

			<thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-1">Select all</label>
                        <input id="cb-select-all-1" type="checkbox">
                    </td>
                    <th scope="col" id="description" class="manage-column column-description">
                        Description
                    </th>
                    <th scope="col" id="description" class="manage-column column-description">
                        Tenants
                    </th>
                    <th scope="col" id="date_due" class="manage-column column-date_due sorted asc">
                        <a href="http://propertyhive.test/wp-admin/edit.php?post_type=key_date&orderby=date_due&order=desc&status=upcoming_and_overdue&filter_action=Filter">
                            <span>Date Due</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th scope="col" id="status" class="manage-column column-status">
                        Status
                    </th>
                </tr>
			</thead>

			<tbody id="the-list">

                <tr id="post-92" class="iedit author-self level-0 post-92 type-key_date status-publish hentry">
                    <th scope="row" class="check-column">
                        <label class="screen-reader-text" for="cb-select-92"> Select Inspection </label>
                        <input id="cb-select-92" type="checkbox" name="post[]" value="92">
                        <div class="locked-indicator">
                            <span class="locked-indicator-icon" aria-hidden="true"></span>
                            <span class="screen-reader-text">“Inspection“ is locked</span>
                        </div>
                    </th>
                    <td class="description column-description" data-colname="Description">
                        <div class="cell-main-content">Inspection</div>
                        <div class="row-actions">
                            <span class="inline hide-if-no-js">
                                <button type="button" class="button-link editinline" aria-label="Quick edit “Inspection” inline" aria-expanded="false">
                                    Quick&nbsp;Edit
                                </button>
                            </span>
                        </div>
                    </td>
                    <td class="date_due column-date_due" data-colname="Date Due">
                        <div class="cell-main-content">Mickey Mouse</div>
                    </td>
                    <td class="date_due column-date_due" data-colname="Date Due">
                        <div class="cell-main-content">13th December 2020</div>
                    </td>
                    <td class="status column-status" data-colname="Status">
                        <div class="cell-main-content">Overdue</div>
                    </td>
                </tr>
			</tbody>

			<tfoot>

                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-2">Select all</label>
                        <input id="cb-select-all-2" type="checkbox">
                    </td>
                    <th scope="col" class="manage-column column-description">
                        Description
                    </th>
                    <th scope="col" class="manage-column column-description">
                        Tenants
                    </th>
                    <th scope="col" class="manage-column column-date_due sorted asc">
                        <a href="http://propertyhive.test/wp-admin/edit.php?post_type=key_date&orderby=date_due&order=desc&status=upcoming_and_overdue&filter_action=Filter">
                            <span>Date Due</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th scope="col" class="manage-column column-status">
                        Status
                    </th>
                </tr>

			</tfoot>

		</table>

		<div class="tablenav bottom">

			<div class="alignleft actions bulkactions">
				<input type="button" id="doaction2" class="button action" value="Add Key Date">
			</div>

			<div class="alignleft actions">
			</div>

            <div class="tablenav-pages one-page">
                <span class="displaying-num">3 items</span>
                <span class="pagination-links">
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
                    <span class="paging-input">
                        <label for="current-page-selector" class="screen-reader-text">Current Page</label>
                        <input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging">
                        <span class="tablenav-paging-text">
                            of <span class="total-pages"> 1 </span>
                        </span>
                    </span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
                </span>
            </div>

			<br class="clear">

		</div>

	</div>

</form>
