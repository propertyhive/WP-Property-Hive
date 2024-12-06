<?php
/**
 * Admin View: Page - Import Properties Dummy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<style type="text/css">
/* Inherited from main import add on */
.row-actions { left:0 !important }
.icon-running-status { margin-right:4px; display:inline-block; vertical-align:middle; margin-top:-3px; height:11px; width:11px; border-radius:50%; background:#d63638 }
.icon-running-status.icon-running { background:#00a32a }

/* Overlay */
.dummy-overlay { position:absolute; top:0; left:-20px; right:0; height:100%; background:rgba(0, 0, 0, 0.5); display:flex; justify-content:center; align-items:flex-start; }
.dummy-overlay .promo-window 
{ 
	background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    width: 596px; 
    margin-top: 50px; /* Position halfway down the viewport */
}
.dummy-overlay .promo-window img { max-width:100% }
.dummy-overlay .modal-content { padding:20px; }
.dummy-overlay .modal-content h2 { margin-top:0; line-height:1.3em }
.dummy-overlay .text-section {  }
.dummy-overlay .text-section h4 { font-size:1.2em; margin-bottom:0 }
.dummy-overlay .text-section img { width:20px; height:20px; margin-right:3px; display:inline-block; vertical-align:middle; }
.dummy-overlay .text-section p { padding-left:28px; margin-top:0.3em }
.dummy-overlay .button { width:100%; }

</style>

<div class="wrap propertyhive">

	<h1>Import Properties</h1>

	<a href="#" class="button" style="float:right">Launch Troubleshooting Wizard <span class="dashicons dashicons-editor-help" style="display:inline-block; vertical-align:middle; margin-top:-2px;"></span></a>
	
	<h3>Active Automatic Imports</h3>
	
	<table class="widefat striped" cellspacing="0">
	    <thead>
	        <tr>
	            <th class="format">Format</th>
	            <th class="details">Details</th>
	            <th class="frequency">Import Frequency</th>
	            <th class="lastran">Last Ran</th>
	            <th class="nextdue">Next Due To Run</th>
	        </tr>
	    </thead>
	    <tbody>
	        <tr>
	            <td class="format">
	                <span class="icon-running-status icon-running"></span>
	                <strong><a href="#">Your CRM</a></strong>                
	                <div class="row-actions">
	                    <a href="#">Pause Import</a>
	                    |
	                    <a href="#">Edit Settings</a>
	                    |
	                    <a href="#">Edit Mappings</a>
	                    |
	                    <a href="#">Logs</a>
	                    |
	                    <span class="trash"><a href="#">Delete</a></span>
	                </div>
	            </td>
	            <td class="details" style="overflow-wrap: break-word; word-wrap:break-word; max-width:300px;">
	            	<strong>API Key:</strong> cvYG8ffdr32425Gdccc
	            </td>
	            <td class="frequency">Hourly</td>
	            <td class="lastran"><?php echo date("jS F Y H:i", strtotime('-2 hours')); ?></td>
	            <td class="nextdue">
	                Today at <?php echo date("H:i", strtotime('+10 mins')); ?>            
	            </td>
	        </tr>
	    </tbody>
	</table>
	<br><a href="#" id="run_now" class="button">Run Now</a><br>
	<br>
	<hr>
	<br>
	<form action="#" name="frmPropertyImportOne" method="post" enctype="multipart/form-data">
	    <h3>Step 1. Create a New Import</h3>
	    <p>Please select whether you would like to do a manual one-off upload, or whether the imports should occur automatically on a regular basis</p>
	    <table class="form-table">
	        <tr valign="top">
	            <th scope="row" class="titledesc">
	                <label for="manual_automatic_manual">Import Type</label>
	            </th>
	            <td class="forminp forminp-text">
	                <label>
	                <input type="radio" name="manual_automatic" id="manual_automatic_automatic" value="automatic" checked />
	                Automatic
	                </label>
	                <span class="description">
	                    <p>Select this if your properties are managed elsewhere and should be automatically imported on a regular basis</p>
	                </span>
	                <br>
	                <label>
	                <input type="radio" name="manual_automatic" id="manual_automatic_manual" value="manual" />
	                Manual
	                </label>
	                <span class="description">
	                    <p>Upload the file and perform a one-off import of properties</p>
	                </span>
	            </td>
	        </tr>
	    </table>
	    <p class="submit">
	        <input name="save" id="save_import_step" class="button-primary" type="submit" value="Continue">
	    </p>
	</form>

</div>

<div class="dummy-overlay">
	
	<div class="promo-window">

		<img src="https://wp-property-hive.com/wp-content/uploads/2015/12/property-import-banner-1024x301.png" alt="Automated property imports">

		<div class="modal-content">

			<h2>Automatically import properties to WordPress from the leading estate agency CRM's</h2>

			<div class="text-section">
				<h4><img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgaWQ9IkxheWVyXzEiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDUxMiA1MTI7IiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCA1MTIgNTEyIiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj48c3R5bGUgdHlwZT0idGV4dC9jc3MiPgoJLnN0MHtmaWxsOiMyQkI2NzM7fQoJLnN0MXtmaWxsOm5vbmU7c3Ryb2tlOiNGRkZGRkY7c3Ryb2tlLXdpZHRoOjMwO3N0cm9rZS1taXRlcmxpbWl0OjEwO30KPC9zdHlsZT48cGF0aCBjbGFzcz0ic3QwIiBkPSJNNDg5LDI1NS45YzAtMC4yLDAtMC41LDAtMC43YzAtMS42LDAtMy4yLTAuMS00LjdjMC0wLjktMC4xLTEuOC0wLjEtMi44YzAtMC45LTAuMS0xLjgtMC4xLTIuNyAgYy0wLjEtMS4xLTAuMS0yLjItMC4yLTMuM2MwLTAuNy0wLjEtMS40LTAuMS0yLjFjLTAuMS0xLjItMC4yLTIuNC0wLjMtMy42YzAtMC41LTAuMS0xLjEtMC4xLTEuNmMtMC4xLTEuMy0wLjMtMi42LTAuNC00ICBjMC0wLjMtMC4xLTAuNy0wLjEtMUM0NzQuMywxMTMuMiwzNzUuNywyMi45LDI1NiwyMi45UzM3LjcsMTEzLjIsMjQuNSwyMjkuNWMwLDAuMy0wLjEsMC43LTAuMSwxYy0wLjEsMS4zLTAuMywyLjYtMC40LDQgIGMtMC4xLDAuNS0wLjEsMS4xLTAuMSwxLjZjLTAuMSwxLjItMC4yLDIuNC0wLjMsMy42YzAsMC43LTAuMSwxLjQtMC4xLDIuMWMtMC4xLDEuMS0wLjEsMi4yLTAuMiwzLjNjMCwwLjktMC4xLDEuOC0wLjEsMi43ICBjMCwwLjktMC4xLDEuOC0wLjEsMi44YzAsMS42LTAuMSwzLjItMC4xLDQuN2MwLDAuMiwwLDAuNSwwLDAuN2MwLDAsMCwwLDAsMC4xczAsMCwwLDAuMWMwLDAuMiwwLDAuNSwwLDAuN2MwLDEuNiwwLDMuMiwwLjEsNC43ICBjMCwwLjksMC4xLDEuOCwwLjEsMi44YzAsMC45LDAuMSwxLjgsMC4xLDIuN2MwLjEsMS4xLDAuMSwyLjIsMC4yLDMuM2MwLDAuNywwLjEsMS40LDAuMSwyLjFjMC4xLDEuMiwwLjIsMi40LDAuMywzLjYgIGMwLDAuNSwwLjEsMS4xLDAuMSwxLjZjMC4xLDEuMywwLjMsMi42LDAuNCw0YzAsMC4zLDAuMSwwLjcsMC4xLDFDMzcuNywzOTguOCwxMzYuMyw0ODkuMSwyNTYsNDg5LjFzMjE4LjMtOTAuMywyMzEuNS0yMDYuNSAgYzAtMC4zLDAuMS0wLjcsMC4xLTFjMC4xLTEuMywwLjMtMi42LDAuNC00YzAuMS0wLjUsMC4xLTEuMSwwLjEtMS42YzAuMS0xLjIsMC4yLTIuNCwwLjMtMy42YzAtMC43LDAuMS0xLjQsMC4xLTIuMSAgYzAuMS0xLjEsMC4xLTIuMiwwLjItMy4zYzAtMC45LDAuMS0xLjgsMC4xLTIuN2MwLTAuOSwwLjEtMS44LDAuMS0yLjhjMC0xLjYsMC4xLTMuMiwwLjEtNC43YzAtMC4yLDAtMC41LDAtMC43ICBDNDg5LDI1Niw0ODksMjU2LDQ4OSwyNTUuOUM0ODksMjU2LDQ4OSwyNTYsNDg5LDI1NS45eiIgaWQ9IlhNTElEXzNfIi8+PGcgaWQ9IlhNTElEXzFfIj48bGluZSBjbGFzcz0ic3QxIiBpZD0iWE1MSURfMl8iIHgxPSIyMTMuNiIgeDI9IjM2OS43IiB5MT0iMzQ0LjIiIHkyPSIxODguMiIvPjxsaW5lIGNsYXNzPSJzdDEiIGlkPSJYTUxJRF80XyIgeDE9IjIzMy44IiB4Mj0iMTU0LjciIHkxPSIzNDUuMiIgeTI9IjI2Ni4xIi8+PC9nPjwvc3ZnPg==" alt=""> Zero data entry</h4>
				<p>Save hours of your valuable time by automating the input and upkeep of property details on your website.</p></div>

			<div class="text-section">
				<h4><img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgaWQ9IkxheWVyXzEiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDUxMiA1MTI7IiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCA1MTIgNTEyIiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj48c3R5bGUgdHlwZT0idGV4dC9jc3MiPgoJLnN0MHtmaWxsOiMyQkI2NzM7fQoJLnN0MXtmaWxsOm5vbmU7c3Ryb2tlOiNGRkZGRkY7c3Ryb2tlLXdpZHRoOjMwO3N0cm9rZS1taXRlcmxpbWl0OjEwO30KPC9zdHlsZT48cGF0aCBjbGFzcz0ic3QwIiBkPSJNNDg5LDI1NS45YzAtMC4yLDAtMC41LDAtMC43YzAtMS42LDAtMy4yLTAuMS00LjdjMC0wLjktMC4xLTEuOC0wLjEtMi44YzAtMC45LTAuMS0xLjgtMC4xLTIuNyAgYy0wLjEtMS4xLTAuMS0yLjItMC4yLTMuM2MwLTAuNy0wLjEtMS40LTAuMS0yLjFjLTAuMS0xLjItMC4yLTIuNC0wLjMtMy42YzAtMC41LTAuMS0xLjEtMC4xLTEuNmMtMC4xLTEuMy0wLjMtMi42LTAuNC00ICBjMC0wLjMtMC4xLTAuNy0wLjEtMUM0NzQuMywxMTMuMiwzNzUuNywyMi45LDI1NiwyMi45UzM3LjcsMTEzLjIsMjQuNSwyMjkuNWMwLDAuMy0wLjEsMC43LTAuMSwxYy0wLjEsMS4zLTAuMywyLjYtMC40LDQgIGMtMC4xLDAuNS0wLjEsMS4xLTAuMSwxLjZjLTAuMSwxLjItMC4yLDIuNC0wLjMsMy42YzAsMC43LTAuMSwxLjQtMC4xLDIuMWMtMC4xLDEuMS0wLjEsMi4yLTAuMiwzLjNjMCwwLjktMC4xLDEuOC0wLjEsMi43ICBjMCwwLjktMC4xLDEuOC0wLjEsMi44YzAsMS42LTAuMSwzLjItMC4xLDQuN2MwLDAuMiwwLDAuNSwwLDAuN2MwLDAsMCwwLDAsMC4xczAsMCwwLDAuMWMwLDAuMiwwLDAuNSwwLDAuN2MwLDEuNiwwLDMuMiwwLjEsNC43ICBjMCwwLjksMC4xLDEuOCwwLjEsMi44YzAsMC45LDAuMSwxLjgsMC4xLDIuN2MwLjEsMS4xLDAuMSwyLjIsMC4yLDMuM2MwLDAuNywwLjEsMS40LDAuMSwyLjFjMC4xLDEuMiwwLjIsMi40LDAuMywzLjYgIGMwLDAuNSwwLjEsMS4xLDAuMSwxLjZjMC4xLDEuMywwLjMsMi42LDAuNCw0YzAsMC4zLDAuMSwwLjcsMC4xLDFDMzcuNywzOTguOCwxMzYuMyw0ODkuMSwyNTYsNDg5LjFzMjE4LjMtOTAuMywyMzEuNS0yMDYuNSAgYzAtMC4zLDAuMS0wLjcsMC4xLTFjMC4xLTEuMywwLjMtMi42LDAuNC00YzAuMS0wLjUsMC4xLTEuMSwwLjEtMS42YzAuMS0xLjIsMC4yLTIuNCwwLjMtMy42YzAtMC43LDAuMS0xLjQsMC4xLTIuMSAgYzAuMS0xLjEsMC4xLTIuMiwwLjItMy4zYzAtMC45LDAuMS0xLjgsMC4xLTIuN2MwLTAuOSwwLjEtMS44LDAuMS0yLjhjMC0xLjYsMC4xLTMuMiwwLjEtNC43YzAtMC4yLDAtMC41LDAtMC43ICBDNDg5LDI1Niw0ODksMjU2LDQ4OSwyNTUuOUM0ODksMjU2LDQ4OSwyNTYsNDg5LDI1NS45eiIgaWQ9IlhNTElEXzNfIi8+PGcgaWQ9IlhNTElEXzFfIj48bGluZSBjbGFzcz0ic3QxIiBpZD0iWE1MSURfMl8iIHgxPSIyMTMuNiIgeDI9IjM2OS43IiB5MT0iMzQ0LjIiIHkyPSIxODguMiIvPjxsaW5lIGNsYXNzPSJzdDEiIGlkPSJYTUxJRF80XyIgeDE9IjIzMy44IiB4Mj0iMTU0LjciIHkxPSIzNDUuMiIgeTI9IjI2Ni4xIi8+PC9nPjwvc3ZnPg==" alt="">  Import from the leading CRMs</h4>
				<p>We support over 50 CRMs and industry-standard formats such as CSV and BLM.</p>
			</div>

			<div class="text-section">
				<h4><img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgaWQ9IkxheWVyXzEiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDUxMiA1MTI7IiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCA1MTIgNTEyIiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj48c3R5bGUgdHlwZT0idGV4dC9jc3MiPgoJLnN0MHtmaWxsOiMyQkI2NzM7fQoJLnN0MXtmaWxsOm5vbmU7c3Ryb2tlOiNGRkZGRkY7c3Ryb2tlLXdpZHRoOjMwO3N0cm9rZS1taXRlcmxpbWl0OjEwO30KPC9zdHlsZT48cGF0aCBjbGFzcz0ic3QwIiBkPSJNNDg5LDI1NS45YzAtMC4yLDAtMC41LDAtMC43YzAtMS42LDAtMy4yLTAuMS00LjdjMC0wLjktMC4xLTEuOC0wLjEtMi44YzAtMC45LTAuMS0xLjgtMC4xLTIuNyAgYy0wLjEtMS4xLTAuMS0yLjItMC4yLTMuM2MwLTAuNy0wLjEtMS40LTAuMS0yLjFjLTAuMS0xLjItMC4yLTIuNC0wLjMtMy42YzAtMC41LTAuMS0xLjEtMC4xLTEuNmMtMC4xLTEuMy0wLjMtMi42LTAuNC00ICBjMC0wLjMtMC4xLTAuNy0wLjEtMUM0NzQuMywxMTMuMiwzNzUuNywyMi45LDI1NiwyMi45UzM3LjcsMTEzLjIsMjQuNSwyMjkuNWMwLDAuMy0wLjEsMC43LTAuMSwxYy0wLjEsMS4zLTAuMywyLjYtMC40LDQgIGMtMC4xLDAuNS0wLjEsMS4xLTAuMSwxLjZjLTAuMSwxLjItMC4yLDIuNC0wLjMsMy42YzAsMC43LTAuMSwxLjQtMC4xLDIuMWMtMC4xLDEuMS0wLjEsMi4yLTAuMiwzLjNjMCwwLjktMC4xLDEuOC0wLjEsMi43ICBjMCwwLjktMC4xLDEuOC0wLjEsMi44YzAsMS42LTAuMSwzLjItMC4xLDQuN2MwLDAuMiwwLDAuNSwwLDAuN2MwLDAsMCwwLDAsMC4xczAsMCwwLDAuMWMwLDAuMiwwLDAuNSwwLDAuN2MwLDEuNiwwLDMuMiwwLjEsNC43ICBjMCwwLjksMC4xLDEuOCwwLjEsMi44YzAsMC45LDAuMSwxLjgsMC4xLDIuN2MwLjEsMS4xLDAuMSwyLjIsMC4yLDMuM2MwLDAuNywwLjEsMS40LDAuMSwyLjFjMC4xLDEuMiwwLjIsMi40LDAuMywzLjYgIGMwLDAuNSwwLjEsMS4xLDAuMSwxLjZjMC4xLDEuMywwLjMsMi42LDAuNCw0YzAsMC4zLDAuMSwwLjcsMC4xLDFDMzcuNywzOTguOCwxMzYuMyw0ODkuMSwyNTYsNDg5LjFzMjE4LjMtOTAuMywyMzEuNS0yMDYuNSAgYzAtMC4zLDAuMS0wLjcsMC4xLTFjMC4xLTEuMywwLjMtMi42LDAuNC00YzAuMS0wLjUsMC4xLTEuMSwwLjEtMS42YzAuMS0xLjIsMC4yLTIuNCwwLjMtMy42YzAtMC43LDAuMS0xLjQsMC4xLTIuMSAgYzAuMS0xLjEsMC4xLTIuMiwwLjItMy4zYzAtMC45LDAuMS0xLjgsMC4xLTIuN2MwLTAuOSwwLjEtMS44LDAuMS0yLjhjMC0xLjYsMC4xLTMuMiwwLjEtNC43YzAtMC4yLDAtMC41LDAtMC43ICBDNDg5LDI1Niw0ODksMjU2LDQ4OSwyNTUuOUM0ODksMjU2LDQ4OSwyNTYsNDg5LDI1NS45eiIgaWQ9IlhNTElEXzNfIi8+PGcgaWQ9IlhNTElEXzFfIj48bGluZSBjbGFzcz0ic3QxIiBpZD0iWE1MSURfMl8iIHgxPSIyMTMuNiIgeDI9IjM2OS43IiB5MT0iMzQ0LjIiIHkyPSIxODguMiIvPjxsaW5lIGNsYXNzPSJzdDEiIGlkPSJYTUxJRF80XyIgeDE9IjIzMy44IiB4Mj0iMTU0LjciIHkxPSIzNDUuMiIgeTI9IjI2Ni4xIi8+PC9nPjwvc3ZnPg==" alt=""> Create imports in seconds</h4>
				<p>Our step-by-step wizard makes setting up an import really easy. Simply choose your format and set your preferences to get up and running.</p>
			</div>
			<br>
			<a href="https://wp-property-hive.com/addons/property-import/?src=wordpress-dummy-page" target="_blank" class="button button-primary button-hero">Get PRO →</a>

		</div>

	</div>

</div>