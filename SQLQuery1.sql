-- Create the users table
CREATE TABLE users (
  user_id UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
  name VARCHAR(200),
  email VARCHAR(255) UNIQUE NOT NULL,
  position VARCHAR(100) NOT NULL,
  department VARCHAR(100) NOT NULL,
  created_at DATETIME DEFAULT GETDATE(),
  updated_at DATETIME DEFAULT GETDATE()
);
GO

-- Create the login table
CREATE TABLE login (
  login_id UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
  user_id UNIQUEIDENTIFIER UNIQUE NOT NULL,
  username VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT GETDATE(),
  updated_at DATETIME DEFAULT GETDATE(),
  FOREIGN KEY (user_id) REFERENCES users (user_id)
);
GO

-- Create the suppliers table
CREATE TABLE suppliers (
  supplier_id UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
  name VARCHAR(255) NOT NULL,
  address VARCHAR(MAX), -- Changed TEXT to VARCHAR(MAX) for modern SQL Server
  vat_type VARCHAR(50) NOT NULL CHECK (vat_type IN ('VAT', 'Non_VAT')),
  contact_person VARCHAR(100),
  contact_number VARCHAR(20),
  tin_no VARCHAR(20),
  created_at DATETIME DEFAULT GETDATE(),
  updated_at DATETIME DEFAULT GETDATE()
);
GO

-- Create the purchase_orders table
CREATE TABLE purchase_orders (
  purchase_order_id UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
  requestor_id UNIQUEIDENTIFIER,
  supplier_id UNIQUEIDENTIFIER,
  purpose VARCHAR(255) NOT NULL,
  purchase_order_no VARCHAR(50) UNIQUE NOT NULL,
  official_receipt_no VARCHAR(50),
  date_requested DATE NOT NULL,
  delivery_date DATE NOT NULL,
  shipping_fee DECIMAL(18, 2), -- Added precision and scale
  discount DECIMAL(18, 2), -- Added precision and scale
  subtotal DECIMAL(18, 2), -- Added precision and scale
  total DECIMAL(18, 2), -- Added precision and scale
  created_at DATETIME DEFAULT GETDATE(),
  updated_at DATETIME DEFAULT GETDATE(),
  FOREIGN KEY (requestor_id) REFERENCES users (user_id),
  FOREIGN KEY (supplier_id) REFERENCES suppliers (supplier_id)
);
GO

-- Create the statuses table
CREATE TABLE statuses (
  status_id UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
  status_name VARCHAR(50) UNIQUE NOT NULL,
  description VARCHAR(MAX), -- Changed TEXT to VARCHAR(MAX)
  color VARCHAR(7) DEFAULT '#6c757d', -- Hex color code for status
  sort_order INT DEFAULT 0, -- Order for display
  created_at DATETIME2 DEFAULT GETDATE(),
  updated_at DATETIME2 DEFAULT GETDATE()
);
GO

-- Create the approvals table
CREATE TABLE approvals (
  approval_id UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
  purchase_order_id UNIQUEIDENTIFIER,
  prepared_by_id UNIQUEIDENTIFIER,
  prepared_at DATETIME NOT NULL,
  verified_by_id UNIQUEIDENTIFIER,
  verified_at DATETIME,
  approved_by_id UNIQUEIDENTIFIER,
  approved_at DATETIME,
  received_by_id UNIQUEIDENTIFIER,
  received_at DATETIME,
  status_id UNIQUEIDENTIFIER NOT NULL,
  remarks VARCHAR(MAX), -- Changed TEXT to VARCHAR(MAX)
  FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders (purchase_order_id),
  FOREIGN KEY (prepared_by_id) REFERENCES users (user_id),
  FOREIGN KEY (verified_by_id) REFERENCES users (user_id),
  FOREIGN KEY (approved_by_id) REFERENCES users (user_id),
  FOREIGN KEY (received_by_id) REFERENCES users (user_id),
  FOREIGN KEY (status_id) REFERENCES statuses (status_id)
);
GO

-- Create the items table
CREATE TABLE items (
  item_id UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
  purchase_order_id UNIQUEIDENTIFIER,
  item_description VARCHAR(MAX) NOT NULL, -- Changed TEXT to VARCHAR(MAX)
  quantity INT NOT NULL, -- INTEGER is an alias for INT in SQL Server
  unit_price DECIMAL(18, 2) NOT NULL, -- Added precision and scale
  total_cost DECIMAL(18, 2) NOT NULL, -- Added precision and scale
  created_at DATETIME DEFAULT GETDATE(),
  updated_at DATETIME DEFAULT GETDATE(),
  FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders (purchase_order_id)
);
GO

-- Create the role_types table
CREATE TABLE role_types (
  role_type_id UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
  user_role_type VARCHAR(50) NOT NULL CHECK (user_role_type IN ('requestor', 'finance_controller', 'department_head', 'authorized_personnel'))
);
GO

-- Create the roles table
CREATE TABLE roles (
  role_id UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
  user_id UNIQUEIDENTIFIER,
  role_type_id UNIQUEIDENTIFIER,
  FOREIGN KEY (user_id) REFERENCES users (user_id),
  FOREIGN KEY (role_type_id) REFERENCES role_types (role_type_id)
);
GO