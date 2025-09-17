SELECT        supplier.name as suppliername, supplier.address, supplier.contact_number, po.purchase_order_id, po.requestor_id, po.supplier_id, po.purpose, po.purchase_order_no, po.official_receipt_no, po.date_requested, po.delivery_date, 
                         po.shipping_fee, po.discount, po.subtotal, po.total, po.created_at, po.updated_at
FROM            db_owner.purchase_orders AS po INNER JOIN
                         db_owner.suppliers AS supplier ON po.supplier_id = supplier.supplier_id
