// Pin structure
struct Pin {
    int number;
    int mode;
    int value;
    bool isConfigured;
    String type;  // Added type field
};

// Array of pins
Pin pins[40]; 