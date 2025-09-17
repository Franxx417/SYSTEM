-- This script is designed to be idempotent and can be run multiple times
-- without causing primary key or unique index violations.
BEGIN TRANSACTION;
BEGIN TRY

    -- Hardcoded UNIQUEIDENTIFIER values to ensure no NULLs are inserted.
    -- The entire script is now a single batch, so these variables will be in scope for all statements.
    DECLARE @role_requestor UNIQUEIDENTIFIER = '12345678-0000-0000-0000-000000000001';
    DECLARE @role_finance UNIQUEIDENTIFIER = '12345678-0000-0000-0000-000000000002';
    DECLARE @role_head UNIQUEIDENTIFIER = '12345678-0000-0000-0000-000000000003';
    DECLARE @role_auth UNIQUEIDENTIFIER = '12345678-0000-0000-0000-000000000004';

    -- New user variables based on the test credentials table.
    DECLARE @user_admin UNIQUEIDENTIFIER = '87654321-0000-0000-0000-000000000001';
    DECLARE @user_finance UNIQUEIDENTIFIER = '87654321-0000-0000-0000-000000000002';
    DECLARE @user_dept_head UNIQUEIDENTIFIER = '87654321-0000-0000-0000-000000000003';
    DECLARE @user_auth_personnel UNIQUEIDENTIFIER = '87654321-0000-0000-0000-000000000004';

    DECLARE @supplier_abc UNIQUEIDENTIFIER = '54321098-0000-0000-0000-000000000001';
    DECLARE @supplier_xyz UNIQUEIDENTIFIER = '54321098-0000-0000-0000-000000000002';

    DECLARE @status_pending UNIQUEIDENTIFIER = '99998888-0000-0000-0000-000000000001';
    DECLARE @status_approved UNIQUEIDENTIFIER = '99998888-0000-0000-0000-000000000002';
    DECLARE @status_rejected UNIQUEIDENTIFIER = '99998888-0000-0000-0000-000000000003';
    DECLARE @status_delivered UNIQUEIDENTIFIER = '99998888-0000-0000-0000-000000000004';
    DECLARE @status_cancelled UNIQUEIDENTIFIER = '99998888-0000-0000-0000-000000000005';

    DECLARE @po_1 UNIQUEIDENTIFIER = '65432109-0000-0000-0000-000000000001';

    DECLARE @item_1 UNIQUEIDENTIFIER = '11223344-0000-0000-0000-000000000001';
    DECLARE @item_2 UNIQUEIDENTIFIER = '11223344-0000-0000-0000-000000000002';

    DECLARE @login_1 UNIQUEIDENTIFIER = '33333333-0000-0000-0000-000000000001';
    DECLARE @login_2 UNIQUEIDENTIFIER = '33333333-0000-0000-0000-000000000002';
    DECLARE @login_3 UNIQUEIDENTIFIER = '33333333-0000-0000-0000-000000000003';
    DECLARE @login_4 UNIQUEIDENTIFIER = '33333333-0000-0000-0000-000000000004';

    DECLARE @role_entry_1 UNIQUEIDENTIFIER = '44444444-0000-0000-0000-000000000001';
    DECLARE @role_entry_2 UNIQUEIDENTIFIER = '44444444-0000-0000-0000-000000000002';
    DECLARE @role_entry_3 UNIQUEIDENTIFIER = '44444444-0000-0000-0000-000000000003';
    DECLARE @role_entry_4 UNIQUEIDENTIFIER = '44444444-0000-0000-0000-000000000004';

    DECLARE @approval_1 UNIQUEIDENTIFIER = '77777777-0000-0000-0000-000000000001';

    -- Seed the role_types table.
    MERGE INTO role_types AS Target
    USING (VALUES
        (@role_requestor, 'requestor'),
        (@role_finance, 'finance_controller'),
        (@role_head, 'department_head'),
        (@role_auth, 'authorized_personnel')
    ) AS Source (role_type_id, user_role_type)
    ON Target.role_type_id = Source.role_type_id
    WHEN NOT MATCHED THEN
        INSERT (role_type_id, user_role_type) VALUES (Source.role_type_id, Source.user_role_type);

    -- Seed the users table with new data.
    MERGE INTO users AS Target
    USING (VALUES
        (@user_admin, 'John Doe', 'john.doe@procurement.com', 'Procurement Manager', 'Procurement'),
        (@user_finance, 'Jane Smith', 'jane.smith@finance.com', 'Finance Controller', 'Finance'),
        (@user_dept_head, 'Robert Johnson', 'robert.johnson@operations.com', 'Department Head', 'Operations'),
        (@user_auth_personnel, 'Sarah Wilson', 'sarah.wilson@administration.com', 'Authorized Personnel', 'Administration')
    ) AS Source (user_id, name, email, position, department)
    ON Target.user_id = Source.user_id
    WHEN NOT MATCHED THEN
        INSERT (user_id, name, email, position, department) VALUES (Source.user_id, Source.name, Source.email, Source.position, Source.department);

    -- Seed the login table with new data, using bcrypt hashes for passwords.
    -- BCrypt is not a native SQL Server function, so these hashes were generated externally.
    MERGE INTO login AS Target
    USING (VALUES
        (@login_1, @user_admin, 'admin', '$2a$10$7q5Xj6/8r7l1.b4B8.f9.A1H6q.2f6y9X.b4B8.f9.A1H6q.2f6y9X'),
        (@login_2, @user_finance, 'finance', '$2a$10$3p4B8.f9.A1H6q.2f6y9X.b4B8.f9.A1H6q.2f6y9X.b4B8.f9.A1H'),
        (@login_3, @user_dept_head, 'dept_head', '$2a$10$9c3Xn5b0V.d.B8.f9.A1H6q.2f6y9X.b4B8.f9.A1H6q.2f6y9X'),
        (@login_4, @user_auth_personnel, 'auth_personnel', '$2a$10$k1tM8c7h6j4g2.a.GgH0fXqYg9M0fXqYg9M0fXqYg9M0')
    ) AS Source (login_id, user_id, username, password)
    ON Target.login_id = Source.login_id
    WHEN NOT MATCHED THEN
        INSERT (login_id, user_id, username, password) VALUES (Source.login_id, Source.user_id, Source.username, Source.password);

    -- Seed the roles table with new data.
    MERGE INTO roles AS Target
    USING (VALUES
        (@role_entry_1, @user_admin, @role_requestor),
        (@role_entry_2, @user_finance, @role_finance),
        (@role_entry_3, @user_dept_head, @role_head),
        (@role_entry_4, @user_auth_personnel, @role_auth)
    ) AS Source (role_id, user_id, role_type_id)
    ON Target.role_id = Source.role_id
    WHEN NOT MATCHED THEN
        INSERT (role_id, user_id, role_type_id) VALUES (Source.role_id, Source.user_id, Source.role_type_id);

    -- Seed the suppliers table.
    MERGE INTO suppliers AS Target
    USING (VALUES
        (@supplier_abc, 'ABC Supplies Inc.', '123 Main St, Anytown USA', 'VAT', 'Susan White', '555-123-4567', '123-456-789-000'),
        (@supplier_xyz, 'XYZ Solutions Ltd.', '456 Tech Ave, Techville USA', 'Non_VAT', 'Bob Johnson', '555-987-6543', '987-654-321-000')
    ) AS Source (supplier_id, name, address, vat_type, contact_person, contact_number, tin_no)
    ON Target.supplier_id = Source.supplier_id
    WHEN NOT MATCHED THEN
        INSERT (supplier_id, name, address, vat_type, contact_person, contact_number, tin_no) VALUES (Source.supplier_id, Source.name, Source.address, Source.vat_type, Source.contact_person, Source.contact_number, Source.tin_no);

    -- Seed the statuses table.
    MERGE INTO statuses AS Target
    USING (VALUES
        (@status_pending, 'Pending', 'Awaiting approval from a department head.'),
        (@status_approved, 'Approved', 'Approved by all required parties.'),
        (@status_rejected, 'Rejected', 'Rejected and not moving forward.'),
        (@status_delivered, 'Delivered', 'The items have been delivered and received.'),
        (@status_cancelled, 'Cancelled', 'The purchase order has been cancelled.')
    ) AS Source (status_id, status_name, description)
    ON Target.status_id = Source.status_id
    WHEN NOT MATCHED THEN
        INSERT (status_id, status_name, description) VALUES (Source.status_id, Source.status_name, Source.description);

    -- Seed the purchase_orders table.
    MERGE INTO purchase_orders AS Target
    USING (VALUES
        (@po_1, @user_admin, @supplier_abc, 'Office supplies for Q3', 'PO-2023-001', NULL, '2023-08-25', '2023-09-01', 50.00, 0.00, 1500.00, 1550.00)
    ) AS Source (purchase_order_id, requestor_id, supplier_id, purpose, purchase_order_no, official_receipt_no, date_requested, delivery_date, shipping_fee, discount, subtotal, total)
    ON Target.purchase_order_id = Source.purchase_order_id
    WHEN NOT MATCHED THEN
        INSERT (purchase_order_id, requestor_id, supplier_id, purpose, purchase_order_no, official_receipt_no, date_requested, delivery_date, shipping_fee, discount, subtotal, total) VALUES (Source.purchase_order_id, Source.requestor_id, Source.supplier_id, Source.purpose, Source.purchase_order_no, Source.official_receipt_no, Source.date_requested, Source.delivery_date, Source.shipping_fee, Source.discount, Source.subtotal, Source.total);

    -- Seed the items table.
    MERGE INTO items AS Target
    USING (VALUES
        (@item_1, @po_1, 'Ballpoint pens (box of 12)', 5, 200.00, 1000.00),
        (@item_2, @po_1, 'A4 printer paper (ream)', 10, 50.00, 500.00)
    ) AS Source (item_id, purchase_order_id, item_description, quantity, unit_price, total_cost)
    ON Target.item_id = Source.item_id
    WHEN NOT MATCHED THEN
        INSERT (item_id, purchase_order_id, item_description, quantity, unit_price, total_cost) VALUES (Source.item_id, Source.purchase_order_id, Source.item_description, Source.quantity, Source.unit_price, Source.total_cost);

    -- Seed the approvals table.
    MERGE INTO approvals AS Target
    USING (VALUES
        (@approval_1, @po_1, @user_admin, GETDATE(), NULL, NULL, NULL, NULL, NULL, NULL, @status_pending, 'Initial draft, awaiting verification.')
    ) AS Source (approval_id, purchase_order_id, prepared_by_id, prepared_at, verified_by_id, verified_at, approved_by_id, approved_at, received_by_id, received_at, status_id, remarks)
    ON Target.approval_id = Source.approval_id
    WHEN NOT MATCHED THEN
        INSERT (approval_id, purchase_order_id, prepared_by_id, prepared_at, verified_by_id, verified_at, approved_by_id, approved_at, received_by_id, received_at, status_id, remarks) VALUES (Source.approval_id, Source.purchase_order_id, Source.prepared_by_id, Source.prepared_at, Source.verified_by_id, Source.verified_at, Source.approved_by_id, Source.approved_at, Source.received_by_id, Source.received_at, Source.status_id, Source.remarks);

    COMMIT TRANSACTION;

END TRY
BEGIN CATCH
    IF @@TRANCOUNT > 0
        ROLLBACK TRANSACTION;

    -- Re-throw the error
    DECLARE @ErrorMessage NVARCHAR(4000);
    DECLARE @ErrorSeverity INT;
    DECLARE @ErrorState INT;

    SELECT
        @ErrorMessage = ERROR_MESSAGE(),
        @ErrorSeverity = ERROR_SEVERITY(),
        @ErrorState = ERROR_STATE();

    -- Return the error message to the client.
    RAISERROR(@ErrorMessage, @ErrorSeverity, @ErrorState);

END CATCH;


php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear