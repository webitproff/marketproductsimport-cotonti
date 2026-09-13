<!-- BEGIN: MAIN -->
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">{PHP.L.marketproductsimport_title}</h4>
        </div>

        <div class="card-body">
            {FILE "{PHP.cfg.system_dir}/admin/tpl/warnings.tpl"}

            <!-- BEGIN: UPLOAD -->
            <div class="mb-4">
                <h5 class="mb-3">{PHP.L.marketproductsimport_form_upload}</h5>

                <form id="uploadForm"
                      action="{EXCELIMPORT_FORM_ACTION}"
                      method="post"
                      enctype="{EXCELIMPORT_FORM_ENCTYPE}">

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold w-25">
                                        {PHP.L.marketproductsimport_select_file}
                                    </td>
                                    <td>
                                        <input type="file"
                                               name="excel_file"
                                               class="form-control"
                                               accept=".xlsx,.csv"
                                               required>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">
                                        {PHP.L.marketproductsimport_max_rows_label}
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {EXCELIMPORT_MAX_ROWS}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">
                                        {PHP.L.marketproductsimport_allowed_formats_label}
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            {EXCELIMPORT_ALLOWED_FORMATS}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-end">
                                        <button type="submit"
                                                class="btn btn-success px-4">
                                            {PHP.L.marketproductsimport_upload}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <!-- END: UPLOAD -->

            <!-- BEGIN: MAPPING -->
            <div class="mb-4">
                <h5 class="mb-2">{PHP.L.marketproductsimport_form_mapping}</h5>

                <p class="mb-3">
                    {PHP.L.marketproductsimport_headers}:
                    <span class="badge bg-danger">
                        {EXCELIMPORT_HEADERS}
                    </span>
                </p>

                <form id="importForm"
                      action="{EXCELIMPORT_MAPPING_ACTION}"
                      method="post">

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="w-50">
                                        {PHP.L.marketproductsimport_field_table}
                                    </th>
                                    <th>
                                        {PHP.L.marketproductsimport_field_excel}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- BEGIN: FIELDS -->
                                <tr>
                                    <td class="fw-semibold">
                                        {FIELD_LABEL}
                                    </td>
                                    <td>
                                        {FIELD_INPUT}
                                    </td>
                                </tr>
                                <!-- END: FIELDS -->
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit"
                                id="startImport"
                                class="btn btn-primary px-4">
                            {PHP.L.marketproductsimport_import}
                        </button>

                        <a href="{EXCELIMPORT_RESET_URL}"
                           class="btn btn-outline-secondary">
                            {PHP.L.marketproductsimport_reset}
                        </a>
                    </div>
                </form>
            </div>
            <!-- END: MAPPING -->

            {MESSAGES}
        </div>
    </div>
</div>
<!-- END: MAIN -->
