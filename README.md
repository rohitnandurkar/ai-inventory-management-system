\# AI Inventory Management System



An AI-powered inventory management system built with PHP, MySQL, and Python.



The system extends a traditional inventory management application with

Linear Regression-based demand forecasting, stock health analysis,

low-stock detection, and intelligent reorder recommendations.



\---



\## Features



\### Inventory Management



\- Product management

\- Category management

\- User management

\- Sales management

\- Automatic stock deduction when a sale is created

\- Stock restoration when a sale is deleted

\- Correct stock adjustment when a sale is edited

\- Sales reports

\- Daily sales reports

\- Monthly sales reports

\- Yearly sales reports



\### AI Inventory Analytics



\- Linear Regression demand prediction

\- Historical sales analysis

\- Future demand forecasting

\- Actual vs Predicted Sales graph

\- Product performance analysis

\- Demand trend analysis

\- Stock coverage calculation

\- Stock risk detection

\- Intelligent low-stock dashboard

\- Reorder point calculation

\- Recommended reorder quantity

\- Fast-moving product analysis

\- Slow-moving product analysis

\- AI inventory recommendations



\---



\## Technology Stack



\### Backend



\- PHP

\- MySQL

\- Apache



\### Machine Learning



\- Python

\- Pandas

\- NumPy

\- Scikit-learn

\- Linear Regression



\### Frontend



\- HTML

\- CSS

\- Bootstrap

\- JavaScript

\- Chart.js



\### Development Environment



\- XAMPP

\- Git

\- GitHub



\---



\# System Architecture



```text

&#x20;               INVENTORY MANAGEMENT SYSTEM

&#x20;                          |

&#x20;         +----------------+----------------+

&#x20;         |                                 |

&#x20;    PHP Application                   MySQL Database

&#x20;         |                                 |

&#x20;         |                            Products

&#x20;         |                            Categories

&#x20;         |                            Sales

&#x20;         |                            Users

&#x20;         |                                 |

&#x20;         +----------------+----------------+

&#x20;                          |

&#x20;                     Sales History

&#x20;                          |

&#x20;                          v

&#x20;                   Python ML Engine

&#x20;                          |

&#x20;                   Daily Sales Demand

&#x20;                          |

&#x20;                          v

&#x20;                 Linear Regression

&#x20;                          |

&#x20;                          v

&#x20;                 Demand Prediction

&#x20;                          |

&#x20;            +-------------+-------------+

&#x20;            |             |             |

&#x20;            v             v             v

&#x20;       Stock Risk    Stock Coverage   Trend

&#x20;            |             |             |

&#x20;            +-------------+-------------+

&#x20;                          |

&#x20;                          v

&#x20;                 Reorder Point

&#x20;                          |

&#x20;                          v

&#x20;               Recommended Order Qty

&#x20;                          |

&#x20;                          v

&#x20;               AI Inventory Dashboard

