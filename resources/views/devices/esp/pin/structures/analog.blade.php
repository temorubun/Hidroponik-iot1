// Struktur untuk pin analog
struct AnalogPin {
    unsigned long lastRead = 0;
    const unsigned long readInterval = 1000; // Read every 1 second
};

AnalogPin analogPins[40]; 